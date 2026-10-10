#!/usr/bin/env python3
"""Non-invasive HTTP/HTML smoke audit. No login, no PII, no POST."""
import concurrent.futures,collections,json,re,sys
from urllib.parse import urljoin,urlparse,urldefrag
import requests
from bs4 import BeautifulSoup
BASE='https://fundart.mskpoeira.com.br'
SOURCE='https://fundart.com.br'
PAGES=[
'/','/acervo/','/noticias/','/editais/','/agenda/','/oficinas/','/a-fundart/','/ouvidoria/','/transparencia/',
'/anexo-i-projeto-arte-para-todos/','/anexo-ii-ficha-inscricao/','/anexo-iii-plano-trabalho/',
'/anexo-iv-capacidade-tecnica/','/anexo-v-fato-impeditivo/','/anexo-vi-recurso/',
'/credenciamento-arte-educadores/','/inscricoes-oficinas/','/tradicao/','/tradicao/comunidades/caicara/',
'/institucional/editais/','/institucional/portal-da-transparencia-3/prestacao-de-contas/',
'/projeto-guri-abre-inscricoes-para-segundo-semestre-de-2016/','/oficina-de-danca-fundart-recebe-13-premios-no-festival-danca-ubatuba/',
'/contato/','/privacidade/','/acessibilidade/','/mapa-do-site/','/wp-login.php','/wp-json/','/robots.txt','/wp-sitemap.xml','/sitemap_index.xml',
'/wp-content/themes/fundart/style.css','/AUDIT-THIS-URL-SHOULD-NOT-EXIST-2026-10-10/'
]
session=requests.Session();session.headers.update({'User-Agent':'FUNDART-ReadOnly-QA/1.0'})
def fetch(url):
 try:
  r=session.get(url,timeout=17,allow_redirects=True)
  return {'status':r.status_code,'url':r.url,'body':r.text[:1500000] if 'text/html' in r.headers.get('Content-Type','') else '', 'headers':dict(r.headers),'error':''}
 except Exception as e:return {'status':0,'url':url,'body':'','headers':{},'error':str(e)[:160]}
def worker(path):return path,fetch(BASE+path)
with concurrent.futures.ThreadPoolExecutor(max_workers=8) as pool:result=dict(pool.map(worker,PAGES))
res={'base':BASE,'urls':{},'security_headers':{},'nav_links':{},'accessibility_markup':{},'source_parity_samples':[],'media_sample':{}}
for path,r in result.items():
 b=r['body'];s=BeautifulSoup(b,'html.parser') if b else None
 res['urls'][path]={'status':r['status'],'redirected_to':r['url'] if r['url']!=BASE+path else None,'error':r['error'] or None,'title':s.title.get_text(' ',strip=True)[:100] if s and s.title else None,'h1_count':len(s.select('h1')) if s else None,'canonical':(s.select_one('link[rel=canonical]') or {}).get('href') if s else None,
 'robots':(s.select_one('meta[name=robots]') or {}).get('content') if s else None,
 'img_total':len(s.select('img')) if s else None,
 'img_missing_alt':sum(not tag.has_attr('alt') for tag in s.select('img')) if s else None,
 'form_count':len(s.select('form')) if s else None}
home=result['/'];headers={k.lower():v for k,v in home['headers'].items()}
for k in ['strict-transport-security','content-security-policy','x-content-type-options','x-frame-options','referrer-policy','permissions-policy','cache-control','server']:
 res['security_headers'][k]=headers.get(k)
for path in ['/','/acervo/']:
 r=result[path];s=BeautifulSoup(r['body'],'html.parser') if r['body'] else None
 if not s:continue
 links=[];external=[];empty=[]
 for a in s.select('a[href]'):
  href=a.get('href','').strip();t=a.get_text(' ',strip=True)
  url=urljoin(BASE+path,href);u=urlparse(url)
  if not t and not a.get('aria-label') and not a.select_one('img[alt]'):empty.append(href[:80])
  if u.netloc==urlparse(BASE).netloc and u.scheme in ('https','http'):
   links.append(urldefrag(url)[0])
  elif u.scheme in ('https','http'):external.append(u.netloc)
 res['nav_links'][path]={'internal_count':len(links),'internal_unique':len(set(links)),'external_hosts_top':collections.Counter(external).most_common(8),'empty_label_links':empty[:8]}
 if path=='/':
  samples=list(dict.fromkeys(links))[:75]
  with concurrent.futures.ThreadPoolExecutor(max_workers=8) as pool:
   checks=dict(pool.map(lambda u:(u,fetch(u)['status']),samples))
  res['nav_links']['/']['checked']=len(checks)
  res['nav_links']['/']['bad_http']=[{'url':u,'status':status} for u,status in checks.items() if status>=400 or status==0][:30]
  imgs=s.select('img[src]')[:20];urls=[urljoin(BASE,i.get('src')) for i in imgs]
  with concurrent.futures.ThreadPoolExecutor(max_workers=8) as pool:images=dict(pool.map(lambda u:(u,fetch(u)['status']),urls))
  res['media_sample']={'checked':len(images),'broken':[{'url':u,'status':c} for u,c in images.items() if c>=400 or c==0],'external_src':sum(urlparse(u).netloc!=urlparse(BASE).netloc for u in images)}
 s_imgs=s.select('img')
 res['accessibility_markup'][path]={'image_without_alt':sum(not x.has_attr('alt') for x in s_imgs),'unlabeled_form_controls':sum(not (x.get('aria-label') or x.get('id') and s.select_one('label[for="'+x.get('id')+'"]') or x.find_parent('label') or x.get('type')=='hidden') for x in s.select('input,textarea,select')),'links_without_accessible_text':len(empty)}
# Fair sample of *content* fidelity, not graphical pixel-perfect similarity.
parity=['/a-fundart/','/institucional/estatuto/','/institucional/editais/','/tradicao/comunidades/caicara/','/tradicao/musica/banda-lira-padre-anchieta/','/projeto-guri-abre-inscricoes-para-segundo-semestre-de-2016/','/oficina-de-danca-fundart-recebe-13-premios-no-festival-danca-ubatuba/']
def normalize(text):
 return re.findall(r'[\wÀ-ÿ]+',text.lower())
def sample(path):
 src=fetch(SOURCE+path);dst=result.get(path) or fetch(BASE+path)
 o={'path':path,'source_http':src['status'],'target_http':dst['status']}
 if src['body'] and dst['body']:
  a=BeautifulSoup(src['body'],'html.parser')
  b=BeautifulSoup(dst['body'],'html.parser')
  sa=a.select_one('.page_content,.single-content,.entry-content')
  tb=b.select_one('main')
  if sa and tb:
   aw=normalize(sa.get_text(' ',strip=True));bw=normalize(tb.get_text(' ',strip=True))
   grams=[' '.join(aw[i:i+5]) for i in range(max(0,len(aw)-4))]
   bset=set(' '.join(bw[i:i+5]) for i in range(max(0,len(bw)-4)))
   o['source_word_count']=len(aw)
   o['content_5gram_coverage']=round(sum(g in bset for g in grams)/max(len(grams),1),3)
   o['source_img_count']=len(sa.select('img'))
   o['target_main_img_count']=len(tb.select('img'))
 return o
with concurrent.futures.ThreadPoolExecutor(max_workers=7) as pool:res['source_parity_samples']=list(pool.map(sample,parity))
print('AUDIT_PUBLIC_JSON='+json.dumps(res,ensure_ascii=False,sort_keys=True))

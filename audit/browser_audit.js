const { chromium }=require('playwright');
const AxeBuilder=require('@axe-core/playwright').default;
(async()=>{
 const base='https://fundart.mskpoeira.com.br';
 const browser=await chromium.launch({headless:true,args:['--no-sandbox']});
 const output={pages:{},interactions:{},errors:[]};
 const targets=['/','/acervo/','/a-fundart/','/ouvidoria/','/anexo-ii-ficha-inscricao/','/anexo-iii-plano-trabalho/','/inscricoes-oficinas/','/credenciamento-arte-educadores/'];
 for(const path of targets){
  const page=await browser.newPage({viewport:{width:1366,height:768}});
  const errs=[];
  page.on('pageerror',err=>errs.push('JS: '+String(err.message).slice(0,170)));
  page.on('console',msg=>{if(msg.type()==='error')errs.push('console: '+msg.text().slice(0,140))});
  try{
   const r=await page.goto(base+path,{waitUntil:'domcontentloaded',timeout:35000});
   await page.waitForTimeout(600);
   const metrics=await page.evaluate(()=>({scrollWidth:document.documentElement.scrollWidth,viewport:innerWidth,images:[...document.images].filter(x=>!x.complete||x.naturalWidth===0).length,links:document.querySelectorAll('a').length,forms:document.querySelectorAll('form').length}));
   const axe=await new AxeBuilder({page}).withTags(['wcag2a','wcag2aa','wcag21a','wcag21aa']).analyze();
   output.pages[path]={http:r.status(),metrics,axe_violations:axe.violations.map(v=>({rule:v.id,impact:v.impact,nodes:v.nodes.length,first_target:v.nodes[0]?.target?.slice(0,2)})).slice(0,20),axe_incomplete:axe.incomplete.length,errors:errs};
   if(path==='/anexo-ii-ficha-inscricao/'){
    const bank=page.locator('select[name=banco]');const pix=page.locator('input[name=pix]');
    output.interactions.bank_options=await bank.locator('option').count();
    await page.locator('select[name=pix_tipo]').selectOption({label:'CPF'});
    await pix.fill('12345678901');output.interactions.pix_cpf_value=await pix.inputValue();
    await page.locator('select[name=pix_tipo]').selectOption({label:'E-mail'});
    await pix.fill('teste@example.com');output.interactions.pix_email_value=await pix.inputValue();
    await page.locator('select[name=opcao1]').selectOption({label:'Bordado'});
    page.once('dialog',d=>d.accept());
    await page.locator('select[name=opcao2]').selectOption({label:'Bordado'});
    output.interactions.duplicate_choice_rejected=(await page.locator('select[name=opcao2]').inputValue())==='';
    const dl=page.waitForEvent('download',{timeout:10000});
    await page.locator('button.fda-download').click();
    output.interactions.download_filename=(await dl).suggestedFilename();
    output.interactions.form_file_input=await page.locator('input[type=file]').count();
   }
   if(path==='/inscricoes-oficinas/'){
    output.interactions.student_submit_disabled=await page.locator('button:has-text("Inscrições online aguardando autorização")').isDisabled();
   }
  }catch(err){output.pages[path]={error:String(err).slice(0,240),other_errors:errs}}
  await page.close();
 }
 for(const width of [375,768]){
  const page=await browser.newPage({viewport:{width,height:800},isMobile:width===375,deviceScaleFactor:1});
  try{
   await page.goto(base+'/',{waitUntil:'domcontentloaded',timeout:35000});
   output.interactions['viewport_'+width]=await page.evaluate(()=>({documentWidth:document.documentElement.scrollWidth,windowWidth:innerWidth,menuToggleVisible:(()=>{const x=document.querySelector('.menu-toggle');return !!x && getComputedStyle(x).display!=='none'})()}));
   if(width===375){
    const btn=page.locator('.menu-toggle');await btn.click();output.interactions.mobile_nav_expanded=await btn.getAttribute('aria-expanded');
   }
  }catch(e){output.interactions['viewport_'+width]={error:String(e).slice(0,200)}}
  await page.close();
 }
 await browser.close();
 console.log('BROWSER_AUDIT_JSON='+JSON.stringify(output));
})().catch(e=>{console.error('AUDIT_FAILED',e);process.exitCode=1});

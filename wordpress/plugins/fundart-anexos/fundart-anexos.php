<?php
/**
 * Plugin Name: FUNDART — Anexos preenchíveis Arte para Todos
 * Description: Anexos II a VI separados, preenchimento local no navegador e impressão/salvamento em PDF, sem transmissão de dados.
 * Version: 1.5.0
 */
defined('ABSPATH') || exit;
function fda_bcb_banks():array {
 $cache=get_transient('fda_bcb_banks_v1');
 if(is_array($cache)&&count($cache)>5)return $cache;
 $url='https://www.bcb.gov.br/pom/spb/ing/ParticipantesSTRIng.csv';
 $response=wp_remote_get($url,['timeout'=>12,'redirection'=>3,'headers'=>['Accept'=>'text/csv']]);
 if(is_wp_error($response)||wp_remote_retrieve_response_code($response)!==200)return is_array($cache)?$cache:[];
 $raw=wp_remote_retrieve_body($response);
 if(strlen($raw)>3000000)return [];
 $lines=preg_split('/\\r\\n|\\r|\\n/',trim($raw));
 if(count($lines)<6)return [];
 $first=$lines[0];
 $delimiter=substr_count($first,';')>=substr_count($first,',')?';':',';
 $header=array_map(static fn($v)=>remove_accents(mb_strtolower(trim($v))),str_getcsv($first,$delimiter));
 $name_index=null;$ispb_index=null;
 foreach($header as $i=>$h){
  if($name_index===null && preg_match('/nome|institu|name|denomina|razao/', $h))$name_index=$i;
  if($ispb_index===null && (str_contains($h,'ispb')||str_contains($h,'identificador')))$ispb_index=$i;
 }
 // Se os titulos nao forem reconhecidos, nao produzir opcoes potencialmente erradas.
 if($name_index===null)return [];
 $banks=[];
 foreach(array_slice($lines,1) as $line){
  $cols=str_getcsv($line,$delimiter);
  $name=isset($cols[$name_index])?sanitize_text_field(trim($cols[$name_index])):'';
  $ispb=$ispb_index!==null?preg_replace('/\\D/','',$cols[$ispb_index]??''):'';
  if($name===''||mb_strlen($name)<3||mb_strlen($name)>140)continue;
  $label=$ispb?($name.' — ISPB '.str_pad($ispb,8,'0',STR_PAD_LEFT)):$name;
  $banks[$label]=$label;
 }
 if(count($banks)<5)return [];
 natcasesort($banks);
 set_transient('fda_bcb_banks_v1',array_values($banks),DAY_IN_SECONDS);
 return array_values($banks);
}
function fda_forms():array {
 $ident=[['nome','Nome do arte-educador'],['cnpj','CNPJ/MF (se MEI)']];
 return [
 'ii'=>['Anexo II — Ficha de Inscrição',[
  ['section','1. Dados do arte-educador'],
  ...$ident,['razao_social','Nome ou razão social (MEI)'],['endereco','Endereço'],
  ['bairro','Bairro'],['cidade','Cidade'],['telefone','Telefone / celular'],['cep','CEP'],
  ['pis','PIS / NIT / INSS'],['inscricao_municipal','Inscrição municipal'],
  ['rg','CI / RG'],['cpf','CPF'],['email','E-mail'],
  ['section','Dados bancários para depósito'],['tipo_conta','Tipo de conta','select','Conta jurídica|Conta física'],
  ['banco','Banco / instituição financeira','bank'],['banco_outro','Outra instituição (se não constar da lista)'],['agencia','Agência'],['conta','Conta'],['pix_tipo','Tipo de chave Pix','select','Automático|CPF|CNPJ|Telefone|E-mail|Chave aleatória'],['pix','Chave Pix (se houver)'],
  ['section','2. Oficinas culturais pretendidas'],['opcao1','Opção 1','select','Bordado|Capoeira|Cavaquinho|Dança (Ballet Clássico)|Dança (Jazz)|Danças Étnicas|Fibras Naturais|Piano|Teatro|Tecelagem|Violão|Proposta Livre'],['opcao2','Opção 2','select','Bordado|Capoeira|Cavaquinho|Dança (Ballet Clássico)|Dança (Jazz)|Danças Étnicas|Fibras Naturais|Piano|Teatro|Tecelagem|Violão|Proposta Livre'],
  ['imagem','Autorização de uso de nome e imagem conforme o edital','select','Não autorizo|Autorizo'],
  ['declaracao','Declaro estar de acordo com as condições do edital','checkbox'],
  ['local_data','Local e data'],['assinatura','Nome do signatário (a assinatura será feita no PDF ou papel)']
 ]],
 'iii'=>['Anexo III — Plano de Trabalho da Oficina Cultural',[
  ['section','1. Dados do arte-educador'],...$ident,
  ['section','2. Descrição da Oficina Cultural'],
  ['oficina','Nome da oficina cultural'],['inicio','Data de início','date'],['fim','Data de término','date'],
  ['identificacao','Identificação da oficina (conforme edital)','textarea'],
  ['objetivos','Objetivos (conforme edital)','textarea'],
  ['descricao','Descrição mensal: execução, conteúdo programático, metodologia, técnicas e faixa etária','textarea'],
  ['justificativa','Justificativa: benefícios econômicos, culturais e sociais à comunidade','textarea'],
  ['section','4. Materiais necessários, coletivos e individuais'],
  ['materiais','Relação de materiais (indicar coletivo/individual, especificação, quantidade e valor unitário estimado)','textarea'],
  ['section','5. Declaração'],
  ['declaracao','Declaro, sob as penas da lei, inexistir impedimento por débito em mora ou inadimplência com o Tesouro ou órgãos públicos, nos termos do Anexo III','checkbox'],
  ['local_data','Local e data'],['assinatura','Nome do arte-educador (assinatura no documento final)']
 ]],
 'iv'=>['Anexo IV — Atestado de Capacidade Técnica',[
  ['section','Empresa contratante'],['razao_contratante','Razão social'],['cnpj_contratante','CNPJ'],
  ['endereco_contratante','Endereço'],['representante_contratante','Representante legal'],['email_contratante','E-mail'],
  ['section','Prestação de serviços atestada'],['empresa_prestadora','Empresa contratada'],['cnpj_prestadora','CNPJ contratado'],
  ['representante_prestadora','Representante da contratada'],['cpf_representante','CPF do representante'],
  ['oficina','Oficina cultural'],['periodo_inicio','Início do período','date'],['periodo_fim','Fim do período','date'],
  ['atestado','A contratante atesta que os serviços foram prestados e que a contratada cumpriu as condições econômicas e técnicas pactuadas','checkbox'],
  ['local_data','Local e data'],['assinatura','Nome do responsável pela assinatura']
 ]],
 'v'=>['Anexo V — Declaração de Ausência de Fato Impeditivo',[
  ['section','Identificação da pessoa jurídica'],['empresa','Razão social'],['cnpj','CNPJ'],
  ['representante','Nome do representante legal'],['cpf','CPF do representante'],
  ['section','Declarações previstas no Anexo V'],
  ['a','Empresa não está impedida de contratar com a Administração Pública','checkbox'],
  ['b','Empresa não foi declarada inidônea pelo Poder Público','checkbox'],
  ['c','Inexiste fato impeditivo ao credenciamento','checkbox'],
  ['d','Inexiste parentesco vedado até terceiro grau conforme o edital','checkbox'],
  ['e','Inexistem impedimentos relativos a contas julgadas irregulares, falta grave ou improbidade nos termos do edital','checkbox'],
  ['f','Não utiliza trabalho de menores em condições vedadas pela legislação, ressalvado aprendiz na forma legal','checkbox'],
  ['local_data','Local e data'],['assinatura','Nome do representante legal signatário']
 ]],
 'vi'=>['Anexo VI — Ficha de Recurso',[
  ['section','Identificação do recorrente'],['nome','Nome do proponente'],['cpf','CPF/MF'],
  ['decisao','Decisão objeto do recurso','select','Habilitação|Classificação|Credenciamento'],
  ['fundamentos','Motivos e fundamentos do recurso','textarea'],
  ['local_data','Local e data'],['assinatura','Nome do educador artístico signatário']
 ]]
 ];
}
function fda_render(string $id):string {
 $forms=fda_forms();
 if(!isset($forms[$id]))return '';
 [$title,$fields]=$forms[$id];
 ob_start();?>
 <section class="fda-wrap" data-anexo="<?php echo esc_attr($id); ?>">
  <div class="fda-notice"><strong>Preparação documental.</strong> Preencha no navegador e utilize <em>Imprimir / Salvar como PDF</em>. Os dados não são enviados nem armazenados no site. O PDF gerado poderá ser assinado posteriormente com certificado ICP-Brasil em um assinador compatível; este portal não realiza assinatura digital.</div>
  <div class="fda-toolbar"><button type="button" class="fda-print">Imprimir / Salvar como PDF</button><button type="button" class="fda-download">Baixar formulário preenchido (HTML)</button><button type="button" class="fda-clear">Limpar</button></div>
  <div class="fda-sheet"><header class="fda-head"><strong>Fundação de Arte e Cultura de Ubatuba — FUNDART</strong><small>Edital nº 30/2025 · Credenciamento nº 03/2025 · Arte para Todos 2026</small><h2><?php echo esc_html($title); ?></h2></header>
  <form class="fda-form" autocomplete="off" onsubmit="return false">
   <?php foreach($fields as $field):
    [$key,$label]=$field;
    if($key==='section'):?><h3><?php echo esc_html($label); ?></h3><?php continue;endif;
    $type=$field[2]??'text';?>
    <label class="fda-field <?php echo $type==='textarea'?'fda-full':''; ?>">
    <span><?php echo esc_html($label); ?></span>
    <?php if($type==='textarea'):?><textarea rows="6" name="<?php echo esc_attr($key); ?>"></textarea>
    <?php elseif($type==='bank'):?>
      <select name="<?php echo esc_attr($key); ?>" class="fda-bank-select">
       <option value="">Selecione a instituição</option>
       <?php foreach(fda_bcb_banks() as $bank):?><option value="<?php echo esc_attr($bank); ?>"><?php echo esc_html($bank); ?></option><?php endforeach;?>
       <option value="outra">Outra instituição / não localizada</option>
      </select>
      <small>Fonte: relação pública de participantes do STR — Banco Central do Brasil. Algumas instituições de pagamento podem não constar da relação.</small>
    <?php elseif($type==='select'):?><select name="<?php echo esc_attr($key); ?>"><option value="">Selecione</option><?php foreach(explode('|',$field[3]) as $option):?><option><?php echo esc_html($option); ?></option><?php endforeach;?></select>
    <?php elseif($type==='checkbox'):?><input type="checkbox" name="<?php echo esc_attr($key); ?>"> <small>Confirmo esta declaração</small>
    <?php else:?><input type="<?php echo $type==='date'?'date':'text'; ?>" name="<?php echo esc_attr($key); ?>" maxlength="350"><?php endif;?>
    </label>
   <?php endforeach;?>
  </form>
  <?php if($id==='ii'): ?>
  <section class="fda-files" aria-label="Documentos complementares">
   <h3>3. Documentos complementares para o credenciamento</h3>
   <p>Selecione os arquivos para conferir sua documentação antes do protocolo presencial. <strong>Os arquivos permanecem neste dispositivo: não são enviados ao servidor, não entram no PDF automaticamente e não substituem sua entrega no protocolo.</strong></p>
   <ul>
   <li>Anexo III — Plano de Trabalho, um por oficina pretendida</li>
   <li>Currículo e formação, certificados e portfólio relacionados à oficina</li>
   <li>Documentação da pessoa jurídica/MEI, CNPJ e documentos de habilitação exigidos nos itens 10 e 11 do edital</li>
   <li>Declaração de ausência de fato impeditivo (Anexo V) e demais comprovantes pertinentes</li>
   </ul>
   <label class="fda-filelabel" for="fda-doc-ii">Selecionar documentos (PDF, DOC, DOCX, JPG, JPEG ou PNG; até 20 MB por arquivo)</label>
   <input id="fda-doc-ii" type="file" class="fda-file-input" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png">
   <div class="fda-file-result" aria-live="polite">Nenhum documento selecionado.</div>
   <p>Ao salvar o formulário em HTML ou PDF, os arquivos anexos não são incorporados. Guarde e apresente separadamente os documentos originais.</p>
  </section>
  <?php endif; ?>
  <footer class="fda-foot"><p>Ubatuba/SP — Praça Nóbrega, 54 · Centro</p><div class="fda-sign">Assinatura do responsável — manuscrita ou digital ICP-Brasil após exportação</div></footer></div>
  <p class="fda-help">Para assinatura digital: salve como PDF e assine em ferramenta compatível com ICP-Brasil. A exportação ou digitação do nome não cria assinatura digital nem protocolo de credenciamento. O item 9.1 do edital prevê entrega presencial.</p>
 </section>
 <?php return (string)ob_get_clean();
}
foreach(array_keys(fda_forms()) as $key){
 add_shortcode('fundart_anexo_'.$key,static fn()=>fda_render($key));
}
add_action('wp_enqueue_scripts',function(){
 wp_register_style('fundart-anexos',false,[],'1.0.0');wp_enqueue_style('fundart-anexos');
 wp_add_inline_style('fundart-anexos','
 .fda-files{margin-top:25px;padding:20px;background:#f0f6fb;border:1px solid #cadce9;border-radius:12px}.fda-files h3{color:#063c6c}.fda-files li{margin:7px 0}.fda-filelabel{display:block;font-weight:750;margin:15px 0 7px}.fda-file-input{display:block;max-width:100%;padding:12px;background:white;border:1px solid #aabfd0;border-radius:8px}.fda-file-result{padding:12px 0;font-size:.88rem;color:#173d5c}.fda-wrap{max-width:1000px;margin:20px auto}.fda-notice{background:#fff0ca;padding:17px;border-left:4px solid #c99b20;border-radius:8px}.fda-toolbar{display:flex;gap:9px;flex-wrap:wrap;margin:18px 0}.fda-toolbar button{border:0;background:#085c9c;color:white;border-radius:8px;padding:11px 16px;font-weight:800;cursor:pointer}.fda-sheet{background:white;border:1px solid #cfdae7;border-radius:14px;padding:25px;box-shadow:0 5px 24px #1234}.fda-head{text-align:center;color:#063c6c}.fda-head strong,.fda-head small{display:block}.fda-head h2{font-size:1.3rem;margin:16px 0}.fda-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px 18px}.fda-form h3{grid-column:1/-1;color:#074e83;border-bottom:2px solid #dbe7ee;margin:20px 0 4px;padding-bottom:7px}.fda-field{display:flex;flex-direction:column;gap:6px;font-weight:700;font-size:.87rem}.fda-field input:not([type=checkbox]),.fda-field select,.fda-field textarea{width:100%;border:1px solid #adc4d6;border-radius:7px;padding:10px;font:inherit;font-weight:400}.fda-full{grid-column:1/-1}.fda-foot{text-align:center;margin-top:42px}.fda-sign{border-top:1px solid #556;max-width:380px;margin:35px auto 0;padding-top:8px;font-size:.78rem}.fda-help{font-size:.83rem;color:#54657a}@media(max-width:620px){.fda-form{grid-template-columns:1fr}.fda-sheet{padding:12px}}@media print{@page{size:A4;margin:13mm}body *{visibility:hidden!important}.fda-sheet,.fda-sheet *{visibility:visible!important}.fda-sheet{position:absolute;left:0;top:0;width:100%;border:0;box-shadow:none;padding:0}.fda-field,.fda-form h3{break-inside:avoid}.fda-field input,.fda-field textarea,.fda-field select{border-bottom:1px solid #777!important;border-radius:0!important}.fda-form{gap:8px 12px}.fda-field textarea{min-height:70px}}
 ');
 wp_register_script('fundart-anexos',false,[],'1.2.0',true);wp_enqueue_script('fundart-anexos');
 wp_add_inline_script('fundart-anexos', "document.addEventListener('change',function(e){if(!e.target.matches('.fda-file-input'))return;var result=e.target.closest('.fda-files').querySelector('.fda-file-result'),files=Array.from(e.target.files),allowed=['pdf','doc','docx','jpg','jpeg','png'];if(files.length>20||files.some(function(f){return f.size>20971520||!allowed.includes(f.name.split('.').pop().toLowerCase())})){e.target.value='';result.textContent='Arquivos não aceitos: limite de 20 arquivos, 20 MB cada, e formatos PDF/DOC/DOCX/JPG/PNG.';return;}result.textContent=files.length?files.map(function(f){return f.name+' ('+Math.round(f.size/1024)+' KB)'}).join(' · '):'Nenhum documento selecionado.';});");
 
 wp_add_inline_script('fundart-anexos',"(function(){\nfunction digits(v){return String(v||'').replace(/\\D/g,'')}\nfunction cpf(v){var d=digits(v).slice(0,11);return d.replace(/^(\\d{3})(\\d)/,'$1.$2').replace(/^(\\d{3})\\.(\\d{3})(\\d)/,'$1.$2.$3').replace(/\\.(\\d{3})(\\d)/,'.$1-$2')}\nfunction cnpj(v){var d=digits(v).slice(0,14);return d.replace(/^(\\d{2})(\\d)/,'$1.$2').replace(/^(\\d{2})\\.(\\d{3})(\\d)/,'$1.$2.$3').replace(/\\.(\\d{3})(\\d)/,'.$1/$2').replace(/(\\/\\d{4})(\\d)/,'$1-$2')}\nfunction phone(v){var d=digits(v).slice(0,11);if(d.length<=2)return d? '('+d : '';return '('+d.slice(0,2)+') '+(d.length>10?d.slice(2,7)+(d.length>7?'-'+d.slice(7):''):d.slice(2,6)+(d.length>6?'-'+d.slice(6):''))}\nfunction apply(el){if(!(el instanceof HTMLInputElement)||el.type==='hidden'||el.type==='date')return;var n=(el.name||'').toLowerCase();var label=el.closest('label');var labelText=label?label.textContent.toLowerCase():'';\nvar kind=/telefone|celular/.test(n)||/telefone|celular/.test(labelText)?'phone':/cnpj/.test(n)||/cnpj/.test(labelText)?'cnpj':/(^|_)cpf($|_)/.test(n)||/\\bcpf\\b/.test(labelText)?'cpf':'';\nif(!kind)return;\nel.setAttribute('inputmode','numeric');el.setAttribute('autocomplete','off');el.maxLength=kind==='phone'?15:kind==='cnpj'?18:14;\nel.dataset.fdaMask=kind;el.placeholder=kind==='phone'?'(12) 99999-9999':kind==='cnpj'?'00.000.000/0000-00':'000.000.000-00';\n}\nfunction format(el){var before=el.value;var fn={cpf:cpf,cnpj:cnpj,phone:phone}[el.dataset.fdaMask];if(!fn)return;var pos=el.selectionStart,digitCount=digits(before.slice(0,pos)).length;el.value=fn(before);\nif(pos!==null){var i=0,count=0;while(i<el.value.length&&count<digitCount){if(/\\d/.test(el.value[i]))count++;i++}try{el.setSelectionRange(i,i)}catch(e){}}}\ndocument.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('.fda-wrap input,.fundart-enrollment input').forEach(apply)});\ndocument.addEventListener('input',function(e){if(e.target&&e.target.dataset&&e.target.dataset.fdaMask)format(e.target)});\n})();");
 wp_add_inline_script('fundart-anexos',"(function(){\nvar digit=function(v){return String(v||'').replace(/[^0-9]/g,'')};\nfunction format(v,type){\n var d=digit(v);\n if(type==='cpf'){d=d.slice(0,11);return d.replace(/^(\\d{3})(\\d)/,'$1.$2').replace(/^(\\d{3}\\.\\d{3})(\\d)/,'$1.$2').replace(/(\\.\\d{3})(\\d)/,'$1-$2')}\n if(type==='cnpj'){d=d.slice(0,14);return d.replace(/^(\\d{2})(\\d)/,'$1.$2').replace(/^(\\d{2}\\.\\d{3})(\\d)/,'$1.$2').replace(/(\\.\\d{3})(\\d)/,'$1/$2').replace(/(\\/\\d{4})(\\d)/,'$1-$2')}\n if(type==='telefone'){\n  d=d.replace(/^55(?=\\d{10,11}$)/,'').slice(0,11);\n  if(d.length<=2)return d?'('+d:'';\n  return '('+d.slice(0,2)+') '+(d.length>10?d.slice(2,7)+(d.length>7?'-'+d.slice(7):''):d.slice(2,6)+(d.length>6?'-'+d.slice(6):''));\n }\n return v;\n}\nfunction kind(raw,selected){\n if(selected==='CPF'||selected==='CNPJ'||selected==='Telefone')return selected.toLowerCase();\n if(selected==='E-mail'||selected==='Chave aleatória')return '';\n var v=raw.trim(),d=digit(v);\n if(v.includes('@')||/[a-zA-Z]/.test(v))return '';\n if(/^\\+55\\D*/.test(v)||/^\\(\\d{2}\\)/.test(v))return 'telefone';\n if(d.length===14)return 'cnpj';\n if(d.length===11)return 'cpf';\n if(d.length===10)return 'telefone';\n return '';\n}\nfunction update(input){\n var form=input.closest('.fda-form'),selector=form&&form.querySelector('select[name=pix_tipo]');\n if(!selector)return;\n var selected=selector.value,mode=kind(input.value,selected);\n input.dataset.pixMode=mode;\n input.removeAttribute('inputmode');\n input.maxLength=120;\n if(mode){\n  input.setAttribute('inputmode','numeric');\n  input.maxLength=mode==='cnpj'?18:15;\n  input.value=format(input.value,mode);\n }\n input.placeholder=selected==='CPF'?'000.000.000-00':selected==='CNPJ'?'00.000.000/0000-00':selected==='Telefone'?'(12) 99999-9999':'CPF, CNPJ, telefone, e-mail ou chave aleatória';\n}\ndocument.addEventListener('DOMContentLoaded',function(){\n document.querySelectorAll('.fda-form input[name=pix]').forEach(update);\n});\ndocument.addEventListener('input',function(e){\n if(!e.target.matches('.fda-form input[name=pix]'))return;\n update(e.target);\n});\ndocument.addEventListener('change',function(e){\n if(!e.target.matches('.fda-form select[name=pix_tipo]'))return;\n var input=e.target.closest('.fda-form').querySelector('input[name=pix]');\n if(input)update(input);\n});\n})();");
 wp_add_inline_script('fundart-anexos', "document.addEventListener('change',function(e){if(!e.target.matches('.fda-wrap[data-anexo=ii] select[name=opcao1],.fda-wrap[data-anexo=ii] select[name=opcao2]'))return;var form=e.target.closest('.fda-form'),first=form.querySelector('[name=opcao1]'),second=form.querySelector('[name=opcao2]');if(first.value&&second.value&&first.value===second.value){e.target.value='';alert('A segunda oficina deve ser diferente da primeira.')}});");
 wp_add_inline_script('fundart-anexos',"(function(){document.querySelectorAll('.fda-wrap').forEach(function(w){var f=w.querySelector('form');w.querySelector('.fda-print').addEventListener('click',function(){window.print()});w.querySelector('.fda-clear').addEventListener('click',function(){if(confirm('Limpar todos os campos?'))f.reset()});w.querySelector('.fda-download').addEventListener('click',function(){var clone=w.querySelector('.fda-sheet').cloneNode(true);var els=f.querySelectorAll('input,textarea,select'),dest=clone.querySelectorAll('input,textarea,select');els.forEach(function(el,i){if(el.type==='checkbox'){dest[i].checked=el.checked;if(el.checked)dest[i].setAttribute('checked','checked');else dest[i].removeAttribute('checked')}else if(el.tagName==='TEXTAREA'){dest[i].textContent=el.value}else if(el.tagName==='SELECT'){Array.from(dest[i].options).forEach(function(o){o.selected=o.value===el.value})}else{dest[i].setAttribute('value',el.value)}});var css=Array.from(document.styleSheets).filter(function(s){return !!s.href&&s.href.indexOf('fundart-anexos')!==-1}).map(function(s){return '<link rel=stylesheet href='+s.href+'>'}).join('');var html='<!doctype html><html lang=pt-BR><meta charset=utf-8><title>Documento FUNDART</title>'+css+'<style>body{font-family:Arial;padding:25px}.fda-sheet{box-shadow:none}</style><body>'+clone.outerHTML+'</body></html>';var a=document.createElement('a'),url=URL.createObjectURL(new Blob([html],{type:'text/html;charset=utf-8'}));a.href=url;a.download='FUNDART-anexo-'+w.dataset.anexo+'.html';a.click();setTimeout(function(){URL.revokeObjectURL(url)},3000)})})})()");
});

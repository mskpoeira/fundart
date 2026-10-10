<?php
/**
 * Plugin Name: FUNDART — Anexos preenchíveis Arte para Todos
 * Description: Anexos II a VI separados, preenchimento local no navegador e impressão/salvamento em PDF, sem transmissão de dados.
 * Version: 1.0.0
 */
defined('ABSPATH') || exit;
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
  ['banco','Banco'],['agencia','Agência'],['conta','Conta'],['pix','Chave Pix (se houver)'],
  ['section','2. Oficinas culturais pretendidas'],['opcao1','Opção 1'],['opcao2','Opção 2'],
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
    <?php elseif($type==='select'):?><select name="<?php echo esc_attr($key); ?>"><option value="">Selecione</option><?php foreach(explode('|',$field[3]) as $option):?><option><?php echo esc_html($option); ?></option><?php endforeach;?></select>
    <?php elseif($type==='checkbox'):?><input type="checkbox" name="<?php echo esc_attr($key); ?>"> <small>Confirmo esta declaração</small>
    <?php else:?><input type="<?php echo $type==='date'?'date':'text'; ?>" name="<?php echo esc_attr($key); ?>" maxlength="350"><?php endif;?>
    </label>
   <?php endforeach;?>
  </form><footer class="fda-foot"><p>Ubatuba/SP — Praça Nóbrega, 54 · Centro</p><div class="fda-sign">Assinatura do responsável — manuscrita ou digital ICP-Brasil após exportação</div></footer></div>
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
 .fda-wrap{max-width:1000px;margin:20px auto}.fda-notice{background:#fff0ca;padding:17px;border-left:4px solid #c99b20;border-radius:8px}.fda-toolbar{display:flex;gap:9px;flex-wrap:wrap;margin:18px 0}.fda-toolbar button{border:0;background:#085c9c;color:white;border-radius:8px;padding:11px 16px;font-weight:800;cursor:pointer}.fda-sheet{background:white;border:1px solid #cfdae7;border-radius:14px;padding:25px;box-shadow:0 5px 24px #1234}.fda-head{text-align:center;color:#063c6c}.fda-head strong,.fda-head small{display:block}.fda-head h2{font-size:1.3rem;margin:16px 0}.fda-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px 18px}.fda-form h3{grid-column:1/-1;color:#074e83;border-bottom:2px solid #dbe7ee;margin:20px 0 4px;padding-bottom:7px}.fda-field{display:flex;flex-direction:column;gap:6px;font-weight:700;font-size:.87rem}.fda-field input:not([type=checkbox]),.fda-field select,.fda-field textarea{width:100%;border:1px solid #adc4d6;border-radius:7px;padding:10px;font:inherit;font-weight:400}.fda-full{grid-column:1/-1}.fda-foot{text-align:center;margin-top:42px}.fda-sign{border-top:1px solid #556;max-width:380px;margin:35px auto 0;padding-top:8px;font-size:.78rem}.fda-help{font-size:.83rem;color:#54657a}@media(max-width:620px){.fda-form{grid-template-columns:1fr}.fda-sheet{padding:12px}}@media print{@page{size:A4;margin:13mm}body *{visibility:hidden!important}.fda-sheet,.fda-sheet *{visibility:visible!important}.fda-sheet{position:absolute;left:0;top:0;width:100%;border:0;box-shadow:none;padding:0}.fda-field,.fda-form h3{break-inside:avoid}.fda-field input,.fda-field textarea,.fda-field select{border-bottom:1px solid #777!important;border-radius:0!important}.fda-form{gap:8px 12px}.fda-field textarea{min-height:70px}}
 ');
 wp_register_script('fundart-anexos',false,[],'1.0.0',true);wp_enqueue_script('fundart-anexos');
 wp_add_inline_script('fundart-anexos',"(function(){document.querySelectorAll('.fda-wrap').forEach(function(w){var f=w.querySelector('form');w.querySelector('.fda-print').addEventListener('click',function(){window.print()});w.querySelector('.fda-clear').addEventListener('click',function(){if(confirm('Limpar todos os campos?'))f.reset()});w.querySelector('.fda-download').addEventListener('click',function(){var clone=w.querySelector('.fda-sheet').cloneNode(true);var els=f.querySelectorAll('input,textarea,select'),dest=clone.querySelectorAll('input,textarea,select');els.forEach(function(el,i){if(el.type==='checkbox'){dest[i].checked=el.checked;if(el.checked)dest[i].setAttribute('checked','checked');else dest[i].removeAttribute('checked')}else if(el.tagName==='TEXTAREA'){dest[i].textContent=el.value}else if(el.tagName==='SELECT'){Array.from(dest[i].options).forEach(function(o){o.selected=o.value===el.value})}else{dest[i].setAttribute('value',el.value)}});var css=Array.from(document.styleSheets).filter(function(s){return !!s.href&&s.href.indexOf('fundart-anexos')!==-1}).map(function(s){return '<link rel=stylesheet href='+s.href+'>'}).join('');var html='<!doctype html><html lang=pt-BR><meta charset=utf-8><title>Documento FUNDART</title>'+css+'<style>body{font-family:Arial;padding:25px}.fda-sheet{box-shadow:none}</style><body>'+clone.outerHTML+'</body></html>';var a=document.createElement('a'),url=URL.createObjectURL(new Blob([html],{type:'text/html;charset=utf-8'}));a.href=url;a.download='FUNDART-anexo-'+w.dataset.anexo+'.html';a.click();setTimeout(function(){URL.revokeObjectURL(url)},3000)})})})()");
});

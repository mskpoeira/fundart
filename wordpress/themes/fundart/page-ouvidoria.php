<?php /* Template Name: Ouvidoria Setorial */ get_header(); ?>
<section class="page-hero"><div class="container"><h1>Ouvidoria Setorial da FUNDART</h1><p>Canal setorial exclusivo para assuntos relacionados aos serviços, atividades, projetos e agentes da Fundação.</p></div></section>
<div class="container page-content">
 <div class="notice"><strong>Atenção: esta é a Ouvidoria Setorial da FUNDART.</strong> Recebe exclusivamente manifestações e pedidos relacionados à própria Fundação. Para outros órgãos municipais, procure a Ouvidoria Geral ou o órgão competente.</div>
 <h2>Como registrar sua demanda</h2>
 <p><a class="btn" href="https://falabr.cgu.gov.br/web/home">Registrar reclamação, denúncia, sugestão, elogio ou solicitação — Fala.BR</a></p>
 <p><a class="btn secondary" href="https://informabr.cgu.gov.br/">Pedido de acesso à informação sobre a FUNDART — LAI/SIC</a></p>
 <h2>Instituição da unidade</h2>
 <a class="decree" href="https://www.ubatuba.sp.gov.br/?p=156904&amp;post_type=diariooficial">Decreto Municipal nº 9.201/2026 — consultar a publicação no Diário Oficial do Município →</a>
 <h2>Etapas do atendimento</h2><ol><li>Registro da manifestação;</li><li>Análise de competência da FUNDART;</li><li>Encaminhamento à unidade responsável;</li><li>Acompanhamento das providências;</li><li>Resposta conclusiva ao manifestante.</li></ol>
 <?php while(have_posts()):the_post();the_content();endwhile; ?>
</div>
<?php get_footer(); ?>
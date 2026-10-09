<?php /* Template Name: Ouvidoria Setorial */ get_header(); ?>
<header class="page-hero"><div class="container">
<nav class="breadcrumbs" aria-label="Você está aqui"><a href="<?php echo esc_url(home_url('/')); ?>">Início</a><span aria-hidden="true">›</span>Ouvidoria Setorial</nav>
<h1>Ouvidoria Setorial da FUNDART</h1>
<p>Canal setorial para manifestações relativas às atividades, aos serviços e aos agentes da Fundação.</p>
</div></header>
<div class="container page-content">
 <article class="institutional-article">
 <div class="notice"><strong>Esta é a Ouvidoria Setorial da FUNDART.</strong> O canal é destinado exclusivamente a manifestações e solicitações relacionadas à Fundação de Arte e Cultura de Ubatuba. Demandas de outros órgãos municipais devem ser encaminhadas ao órgão ou canal competente.</div>
 <h2>Como registrar sua demanda</h2>
 <p><a class="btn" href="https://falabr.cgu.gov.br/web/home">Registrar manifestação — Fala.BR</a></p>
 <p><a class="btn secondary" href="https://falabr.cgu.gov.br/web/home">Solicitar acesso à informação da FUNDART — Fala.BR</a></p>
 <h2>Instituição da Ouvidoria Setorial</h2>
 <a class="decree" href="https://www.ubatuba.sp.gov.br/?p=156904&amp;post_type=diariooficial">Decreto Municipal nº 9.201/2026 — consultar publicação no Diário Oficial do Município →</a>
 <h2>Etapas do atendimento</h2>
 <ol><li>Registro da manifestação;</li><li>Verificação de competência da FUNDART;</li><li>Encaminhamento à unidade responsável;</li><li>Acompanhamento das providências;</li><li>Resposta ao manifestante.</li></ol>
 <?php while(have_posts()):the_post();the_content();endwhile; ?>
 </article>
</div>
<?php get_footer(); ?>

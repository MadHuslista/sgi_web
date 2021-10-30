{% extends "base.twig" %}
{% block title %}Página No Encontrada{% endblock %}
{% block head %}

{% endblock %}
{% block contentheader %}<h1>Página No Encontrada</h1>{% endblock %}
{% block content %}

		<section class="content">
			<!-- Main row -->
			<div class="row">
				<!-- Left col -->

				<section class="col-lg-12 text-center">
					  <img src="{{ baseUrl() }}/img/error-404.png" style="margin: 0 auto;" class="img-responsive">
						<p>La página a la que intenta acceder no existe o no se encuentra disponible.<br>
						Puede intentar <a href="#" onclick="window.history.go(-1); return false;">Volver a la página anterior</a> o ir a la <a href="{{siteUrl('/')}}">Página de inicio.</a></p>
							<p>Si está seguro que esta página debiese existir, puede escribir un email a notificando de la situación</p>
				</section><!-- /.Left col -->
				<!-- right col (We are only adding the ID to make the widgets sortable)-->
			</div><!-- /.row (main row) -->

		</section><!-- /.content -->
{% endblock %}
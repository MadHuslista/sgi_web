{% extends "base.twig" %}
{% block title %}Acceso Denegado{% endblock %}
{% block head %}

{% endblock %}
{% block contentheader %}<h1>Acceso Denegado</h1>{% endblock %}
{% block content %}

		<section class="content">
			<!-- Main row -->
			<div class="row">
				<!-- Left col -->

				<section class="col-lg-12 text-center">
					  <img src="{{ baseUrl() }}/img/error-404.png" style="margin: 0 auto;" class="img-responsive">
					  	<p>{{ $exception->getMessage() }}</p>
						<p>Usted está tratando de acceder a una página sin la autorización adecuada. Asegúrese que inició sesión y tiene los permisos necesarios para ver su contenido.<br>
						Puede intentar <a href="#" onclick="window.history.go(-1); return false;">Volver a la página anterior</a> o ir a la <a href="{{siteUrl('/')}}">Página de inicio</a>
				</section><!-- /.Left col -->
				<!-- right col (We are only adding the ID to make the widgets sortable)-->
			</div><!-- /.row (main row) -->

		</section><!-- /.content -->
{% endblock %}
{{ $exception->getMessage() }}
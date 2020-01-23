<?php die(); ?>
<!doctype html>
<html>
	<head>
		<title>Projeto Corujinha</title>
		<?php include('include/meta.html'); ?>
	</head>
	<body>
				
		<?php include('include/header.html'); ?>

		<section class="site-section" id="fiscalize">
			<div class="middle">
				<div>
					<h1>Fiscalize</h1>		
				</div>
				<p class="prestacao-contas">
					Prestação de contas
					<a href="fiscalize/fiscalize.csv"><i class="fa fa-table"></i> Download da planilha</a>
				</p>
				
				<?php include('include/contas.php'); ?>
				
			</div>
		</section>

		

		<?php 
			include('include/footer.html'); 
			include('include/scripts.html'); 
		?>
		
		<script src="assets/js/fiscalize.js"></script>

	</body>
</html>
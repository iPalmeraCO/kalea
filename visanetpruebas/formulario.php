<!DOCTYPE html>
<html>
<head>
	<title> Pruebas Visa Net GT</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
	<style type="text/css">
		.labelinput {
			font-weight: bold;
		}
	</style>
</head>
<body>
	<div class="container">
		<form action="controller_payment.php" method="post">
			<h1> TEST VISANET </h1>
			<div class="row">
				<div class="row inputforms">
					<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
						<p class="labelinput"> N° Tarjeta </p>
					</div>
					<div class="col-lg-10 col-md-10 col-sm-6 col-xs-12">
						<input type="text" name="tarjeta">
					</div>
				</div>
				<div class="row inputforms">
					<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
						<p class="labelinput"> Nombre titular </p>
					</div>
					<div class="col-lg-10 col-md-10 col-sm-6 col-xs-12">
						<input type="text" name="nombre">
					</div>
				</div>
				<div class="row inputforms">
					<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
						<p class="labelinput"> Fecha de vencimiento  </p>
					</div>
					<div class="col-lg-10 col-md-10 col-sm-6 col-xs-12">
						<input type="text" name="vence">
					</div>
				</div>
				<div class="row inputforms">
					<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
						<p class="labelinput"> Cvv </p>
					</div>
					<div class="col-lg-10 col-md-10 col-sm-6 col-xs-12">
						<input type="text" name="cvv">
					</div>
				</div>

				<div class="row inputforms">
					<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
						<p class="labelinput"> Email </p>
					</div>
					<div class="col-lg-10 col-md-10 col-sm-6 col-xs-12">
						<input type="email" name="cvv">
					</div>
				</div>
				<div class="row inputforms">
					<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
						<p class="labelinput"> Número de cuotas </p>
					</div>
					<div class="col-lg-10 col-md-10 col-sm-6 col-xs-12">
						<select name="vc">
							<option value="VC03"> 3 Meses</option>
							<option value="VC06"> 6 Meses</option>
							<option value="VC09"> 9 Meses</option>
							<option value="VC12"> 12 Meses</option>
							<option value="VC15"> 15 Meses</option>
							<option value="VC18"> 18 Meses</option>		
						</select>
					</div>
				</div>				
				<input type="submit" name="Enviar">
			</div>	
		</form>
	</div>
</body>
</html>
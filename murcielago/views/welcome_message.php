<!DOCTYPE html>
<html lang="en">
<head>
  <title>Bootstrap Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
</head>
<body>
 
<div class="container">
  <h2>Panels with Contextual Classes</h2>    
    <div class="panel panel-success">
      <div class="panel-heading">Panel with panel-success class</div>
      <div class="panel-body">
      	<div class="form-group">
      		<?php echo form_open('welcome/sys_ad'); ?>
      		<div class="form-group">
      			<label>Pilih Kelas</label>
      			<select class="form-control" name="kelas">
      				<option value="">Pilh Kelas</option>
      				<?php 
      				foreach($kelas as $k){
      				?>
      				<option value="<?php echo $k->kd_kelas ?>"><?php echo $k->kode_kelas ?></option>
      				<?php } ?>
      			</select>
      		</div>
      		<div class="form-group">
				<button type="submit" class="btn btn-primary">Tambah</button>      			
      		</div>
      		<?php echo form_close(); ?>
      	</div>
      </div>
    </div>
</div>

</body>
</html>

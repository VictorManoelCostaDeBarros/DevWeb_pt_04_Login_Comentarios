<?php
	$url = explode('/',$_GET['url']);
	

	$verifica_categoria = MySql::conectar()->prepare("SELECT * FROM `tb_site.categorias` WHERE slug = ?");
	$verifica_categoria->execute(array($url[1]));
	if($verifica_categoria->rowCount() == 0){
		Painel::redirect(INCLUDE_PATH.'noticias');
	}
	$categoria_info = $verifica_categoria->fetch();

	$post = MySql::conectar()->prepare("SELECT * FROM `tb_site.noticias` WHERE slug = ? AND categoria_id = ?");
	$post->execute(array($url[2],$categoria_info['id']));
	if($post->rowCount() == 0){
		Painel::redirect(INCLUDE_PATH.'noticias');
	}

	//É POR QUE MINHA NOTICIA EXISTE
	$post = $post->fetch();

?>
<section class="noticia-single">
	<div class="center">
	<header>
		<h1><i class="fa fa-calendar"></i> <?php echo $post['data'] ?> - <?php echo $post['titulo'] ?></h1>
	</header>
	<article>
		<?php echo $post['conteudo']; ?>
	</article>
	<?php 
		if(Painel::Logado() == false){
	?>
		<div class="erro-login">
			<p><i class="fa fa-times"></i> Você precisa estar logado para comentar, clique <a href="<?php echo INCLUDE_PATH_PAINEL ?>">aqui</a> para efetuar o login.</p>
		</div>
	<?php }else{ ?>
		<?php 
			// Inserção dos comentarios na data base.
			if(isset($_POST['postar_comentario'])){
				$nome = $_SESSION['nome']; 
				$comentario = $_POST['mensagem'];
				$noticia_id = $_POST['noticia_id']; 

				$sql = MySql::conectar()->prepare("INSERT INTO `tb_site.comentarios` VALUES (null,?,?,?)");
				$sql->execute(array($nome,$comentario,$noticia_id));
				echo '<script>alert("Comentario realizado com sucesso!")</script>';
			}
		?>
		<h2 class="postar-comentario">Faça um comentário <i class="fa fa-comment"></i></h2>
		<form method="post">
			<input type="text" name="nome" value="<?php echo $_SESSION['nome']; ?>" disabled>
			<textarea name="mensagem" placeholder="Seu comentário..." required></textarea>
			<input type="hidden" name="noticia_id" value="<?php echo $post['id']; ?>">
			<input type="submit" name="postar_comentario" value="Comentar!">
		</form>
		<br>
		<h2 class="postar-comentario">Comentários <i class="fa fa-comment"></i></h2>
		<?php 
			$comentarios = MySql::conectar()->prepare("SELECT * FROM `tb_site.comentarios` WHERE noticia_id = ?");
			$comentarios->execute(array($post['id']));
			$comentarios = $comentarios->fetchAll();
			foreach ($comentarios as $key => $value) {
				
		?>
		<div class="box-comment-noticia">
			<h3><?php echo $value['nome']; ?></h3>
			<p><?php echo $value['comentario']; ?></p>
			<h2 class="postar-comentario">Respostas</h2>
			<div style="border: 1px solid #ccc;padding:8px;" class="respostas">
				<?php 
					$comentario_id = $value['id'];
					$respostas = MySql::conectar()->prepare("SELECT * FROM `tb_site.resposta_comentarios` WHERE comentario_id = ?");
					$respostas->execute(array($comentario_id));
					$respostas = $respostas->fetchAll();
					foreach ($respostas as $key => $value) {
				?>
				<h3><?php echo $value['nome']; ?></h3>
				<p style="border-bottom: 1px dotted #ccc;"><?php echo $value['comentario']; ?></p>
				<?php } ?>
			</div>
			<form method="post">
				<?php 
					if(isset($_POST['resposta_comentario'])){
						$comentario_id = $_POST['comentario_id'];
						$nome = $_SESSION['nome']; 
						$comentario = $_POST['rcomentario'];
						$sql = MySql::conectar()->prepare("INSERT INTO `tb_site.resposta_comentarios` VALUES (null,?,?,?)");
						$sql->execute(array($comentario_id,$nome,$comentario));
						echo '<script>alert("Resposta realizado com sucesso!")</script>';	
					}
				?>
				<h2 class="postar-comentario">Comente</h2>
				<input type="text" name="nome" value="<?php echo $_SESSION['nome']; ?>" disabled>
				<textarea name="rcomentario" placeholder="Seu comentário..." required></textarea>
				<input type="hidden" name="comentario_id" value="<?php echo $value['id']; ?>">
				<input type="submit" name="resposta_comentario" value="Responder!">
			</form>
		</div>
		<?php } ?>
	<?php } ?>
	</div>
</section>


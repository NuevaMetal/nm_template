<?php
class AnaliticaHtml {
	public $user;
	public $analiticas;

	public function __construct() {
	}

	public function setUser($user) {
		$this->user = $user;
	}

	public function setAnaliticas($analiticas) {
		$this->analiticas = $analiticas;
	}

	/**
	 * Pintar el index con la tabla de me gustas del usuario actual
	 *
	 * @param IndexModel $indexModel
	 */
	public function render() {?>
		<div id="page" class="container-fluid">
		<h1>Analítica y seguimiento de usuarios </h1>
		<p>Analítica de usuarios por día</p>
		<div>
			<table class="table table-bordered">
				<tr>
					<th>ID</th>
					<th>user_id</th>
					<th>created_id</th>
					<th>updated_id</th>
				</tr>
			<?php foreach($this->analiticas as $a):?>
				<tr>
					<td><?php echo $a->ID; ?></td>
					<td><?php echo $a->user_id; ?></td>
					<td><?php echo $a->created_at; ?></td>
					<td><?php echo $a->updated_at; ?></td>
				</tr>
				<tr><td colspan="4">
					<p>Seguimiento de Usuarios por post:</p>
					<table class="table table-bordered">
						<tr>
							<th>ID</th>
							<th>analitica_id</th>
							<th>post_id</th>
							<th>total</th>
							<th>ip</th>
							<th>created_id</th>
							<th>updated_id</th>
						</tr>
					<?php foreach($a->getSeguimientos() as $s):?>
						<tr>
							<td><?php echo $s->ID; ?></td>
							<td><?php echo $s->analitica_id; ?></td>
							<td><?php echo $s->post_id; ?></td>
							<td><?php echo $s->total; ?></td>
							<td><?php echo substr($s->ip,0,5).'.***'; ?></td>
							<td><?php echo $s->created_at; ?></td>
							<td><?php echo $s->updated_at; ?></td>
						</tr>
					<?php endforeach;?>
					</table>
					</td>
				</tr>
			<?php endforeach;?>
			</table>
		</div>

	<?php
	}

}


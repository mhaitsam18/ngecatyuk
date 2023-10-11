<header class="mb-3">
    <a href="#" class="burger-btn d-block d-xl-none">
        <i class="bi bi-justify fs-3"></i>
    </a>
</header>
<div class="page-heading">
    <h3><?= $title ?></h3>
</div>
<div class="page-content">
    <section class="row">
        <?= $this->session->flashdata('message'); ?>
        <div class="flash-data" data-flashdata="<?= $this->session->flashdata('flash'); ?>" data-objek="Data Kategori"></div>
        <?= form_error('kategori','<div class="alert alert-danger" role="alert">','</div>'); ?>
        <div class="row">
        	<div class="col-lg-6">
    		  <a href="" class="btn btn-primary mb-3 newKategoriModalButton" data-toggle="modal" data-target="#newKategoriModal">Tambah Kategori</a>
            	<table class="table table-hover">
        			<thead>
        				<tr>
        					<th scope="col">#</th>
                            <th scope="col">Kategori</th>
        					<th scope="col">Action</th>
        				</tr>
        			</thead>
        			<tbody>
    					<?php $no=1; ?>
        				<?php foreach ($kategori as $key): ?>
            				<tr>
            					<th scope="row"><?= $no ?></th>
                                <td><?= $key['kategori'] ?></td>
            					<td>
            						<a href="<?= base_url("DataMaster/updateKategori/$key[id_kategori]"); ?>" class="badge bg-success updateKategoriModalButton" data-toggle="modal" data-target="#newKategoriModal" data-id="<?=$key['id_kategori']?>">Edit</a>
            						<a href="<?= base_url("DataMaster/deleteKategori/$key[id_kategori]"); ?>" class="badge bg-danger tombol-hapus" data-hapus="Kategori">Delete</a>
            					</td>
            				</tr>
            			<?php $no++; ?>
        				<?php endforeach ?>
        			</tbody>
        		</table>
        	</div>
        </div>

    </section>
    <!-- /.container-fluid -->

</div>
<!-- End of Main Content -->

<!-- Modal -->
<div class="modal fade" id="newKategoriModal" tabindex="-1" aria-labelledby="newKategoriModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="newKategoriModalLabel">Tambah Kategori</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form action="<?= base_url('DataMaster/Kategori') ?>" method="post">
				<input type="hidden" name="id_kategori" id="id_kategori">
    			<div class="modal-body">
    				<div class="form-group">
    					<input type="text" class="form-control" id="kategori" name="kategori" placeholder="Kategori">
                        <?= form_error('kategori','<small class="text-danger pl-3">','</small>'); ?>
    				</div>
    			</div>
    			<div class="modal-footer">
    				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
    				<button type="submit" class="btn btn-primary">Add</button>
    			</div>
			</form>
		</div>
	</div>
</div>


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
		<div class="flash-data" data-flashdata="<?= $this->session->flashdata('flash'); ?>" data-objek="Status Pemesanan"></div>
        <div class="col-lg-12">
			<h3 class="h3 mt-5">Pesanan Customer</h3>
			<table class="table table-bordered" style="background-color: white;" id="dataTable">
				<thead>
					<tr>
						<th scope="col">#</th>
						<th scope="col">Kode Bayar</th>
						<th scope="col">Nama Penerima</th>
						<th scope="col">Total</th>
						<th scope="col">Metode Bayar</th>
						<th scope="col">Status</th>
						<th scope="col" style="max-width: 150px; min-width: 90px;">Aksi</th>
					</tr>
				</thead>
				<tbody>
					<?php $no=0; ?>
					<?php foreach ($checkout as $row): ?>
						<tr>
							<th scope="row"><?= ++$no ?></th>
							<td><?= $row['kode_bayar'] ?></td>
							<td><?= $row['nama_penerima'] ?></td>
							<td><?= 'Rp.'.number_format($row['total_harga'],2,',','.') ?></td>
							<td><?= $row['metode_bayar'] ?></td>
							<td><?= $row['status'] ?></td>
							<td>
								<?php if ($row['status'] == 'Belum dibayar'): ?>
									<a href="<?= base_url('Transaksi/updateStatusPesanan/').$row['idc'].'/dibatalkan' ?>" class="badge bg-danger tombol-yakin">Batalkan</a>
								<?php endif ?>
								<a href="<?= base_url('Transaksi/updateStatusPesanan/').$row['idc'].'/lunas' ?>" class="badge bg-success">Lunas</a>
								<a href="<?= base_url('Transaksi/detailPesanan/').$row['idc'] ?>" class="badge bg-primary">Detail</a>
								<?php if ($row['status'] == 'Sudah dibayar'): ?>
									<a href="<?= base_url('Transaksi/updateStatusPesanan/').$row['idc'].'/dikirim' ?>" class="badge bg-info tombol-yakin">Pesanan dikirim</a>
								<?php endif ?>
							</td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>
	</section>
</div>
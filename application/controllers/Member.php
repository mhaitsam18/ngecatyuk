<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Member extends CI_Controller {

	public function __construct()
	{
		parent:: __construct();
		is_logged_in();
		$this->load->library('form_validation');
		$this->load->model('Member_model');
		$this->load->model('Produk_model');
		date_default_timezone_set('Asia/Jakarta');
	}

	public function index()
	{
		$data['title'] = "Beranda";
		$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
		$data['dashboard'] = $this->db->get('dashboard')->row_array();
		$this->load->view('layouts/header', $data);
		$this->load->view('layouts/sidebar', $data);
		$this->load->view('member/index', $data);
		$this->load->view('layouts/footer');
	}

	public function janikLampung()
	{
		$data['dashboard'] = $this->db->get('dashboard')->row_array();
		$data['title'] = "JanikLampung";
		$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
		$data['kurir'] = $this->db->get('kurir')->result_array();
		$config['base_url'] = base_url('Member/janiklampung/');
		// $config['total_rows'] = $this->Produk_model->countAllProduk();
		if ($this->input->post('keyword')) {
			$data['keyword'] = $this->input->post('keyword');
			$this->session->set_userdata('keyword', $data['keyword']);
		} else{
			// $data['keyword'] = null;
			$data['keyword'] = $this->session->set_userdata('keyword');
		}
		if ($this->input->post('kategori')) {
			if ($this->input->post('kategori') != 'All Category') {
				$data['pilih_kategori'] = $this->input->post('kategori');
				$this->session->set_userdata('pilih_kategori', $data['pilih_kategori']);
			} else{
				$data['pilih_kategori'] = null;
				$this->session->set_userdata('pilih_kategori', $data['pilih_kategori']);
			}
		} else{
			// $data['pilih_kategori'] = null;
			$data['pilih_kategori'] = $this->session->userdata('pilih_kategori');
		}
		$this->db->like('nama_produk', $data['keyword']);
		$this->db->like('id_kategori', $data['pilih_kategori']);
		$this->db->from('produk');
		$config['total_rows'] = $this->db->count_all_results();
		$data['total_rows'] = $config['total_rows'];
		$config['per_page'] = 3;
		$config['num_links'] = 2;

		//styling
		$config['full_tag_open'] = '<nav aria-label="Page pagination example"><ul class="pagination justify-content-center">';
		$config['full_tag_close'] = '</ul></nav>';

		$config['first_link'] = 'First';
		$config['first_tag_open'] = '<li class="page-item">';
		$config['first_tag_close'] = '</li>';

		$config['last_link'] = 'Last';
		$config['last_tag_open'] = '<li class="page-item">';
		$config['last_tag_close'] = '</li>';

		if ($config['total_rows']-$this->uri->segment(3)>7) {
		  $config['next_link'] = '&raquo';
		} else{
		  $config['next_link'] = 'Next';
		}
		$config['next_tag_open'] = '
								<li class="page-item">';
		$config['next_tag_close'] = '</li>';

		if ($this->uri->segment(3)>7) {
		  $config['prev_link'] = '&laquo';
		} else{
		  $config['prev_link'] = 'Prev';
		}
		$config['prev_tag_open'] = '<li class="page-item">';
		$config['prev_tag_close'] = '</li>';
		if (empty($this->uri->segment(3))) {
			$prev = '<li class="page-item disabled"><span class="page-link">Prev</span></li>';
			$next = '';
		} elseif ($config['total_rows']-$this->uri->segment(3)<3) {
			$prev = '';
			$next = '<li class="page-item disabled"><span class="page-link">Next</span></li>';
		} else {
			$next = '';
			$prev = '';
		}
		$config['cur_tag_open'] = $prev.'<li class="page-item active" aria-current="page"><span class="page-link">';
		$config['cur_tag_close'] = '</span></li>'.$next;

		$config['num_tag_open'] = '<li class="page-item">';
		$config['num_tag_close'] = '</li>';

		$config['attributes'] = array('class' => 'page-link');

		// $config['display_pages'] = TRUE;
		// $config['attributes']['rel'] = FALSE;
		$this->pagination->initialize($config);
		$data['start'] = $this->uri->segment(3);
		$data['produk'] = $this->Produk_model->getProdukByLimit($config['per_page'],$data['start'], $data['keyword'], $data['pilih_kategori']);
		$data['kategori'] = $this->db->get('kategori')->result_array();
		$this->load->view('layouts/header', $data);
		$this->load->view('layouts/sidebar', $data);
		$this->load->view('member/produk-janik-lampung', $data);
		$this->load->view('layouts/footer');
	}
	public function tambahKeranjang($id_produk, $rowid = '',$area = '')
	{

		if (!$this->session->userdata('cart_checkout', 'cart_checkout')) {
			$this->session->unset_userdata('cart_supply');
			$this->session->set_userdata('cart_checkout', 'cart_checkout');
			$this->cart->destroy();
		}
		$produk = $this->Produk_model->getProdukById($id_produk);
		$keranjang = $this->cart->get_item($rowid);
		$rowid2 = get_rowid_cart($id_produk);
		$keranjang2 = $this->cart->get_item($rowid2);
		if ($keranjang) {
			if ($produk['stok'] <= $keranjang['qty']) {
				$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
					Mohon Maaf, Stok Produk tidak mencukupi!
					</div>');
				if ($area == 'keranjang') {
					redirect('Member/keranjang');
				}
				redirect('Member/janikLampung');
			}
		} elseif ($produk['stok'] <= 0) {
			$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
				Mohon Maaf, Stok Produk tidak mencukupi!
				</div>');
			if ($area == 'keranjang') {
				redirect('Member/keranjang');
			}
			redirect('Member/janikLampung');
		} elseif ($keranjang2) {
			if ($produk['stok'] <= $keranjang2['qty']) {
				$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
					Mohon Maaf, Stok Produk tidak mencukupi!
					</div>');
				if ($area == 'keranjang') {
					redirect('Member/keranjang');
				}
				redirect('Member/janikLampung');
			}
		}
		$data = array(
	        'id'      => $produk['id_produk'],
	        'qty'     => 1,
	        'price'   => $produk['harga'],
	        'name'    => $produk['nama_produk'],
	        'gambar'    => $produk['gambar']
	        // 'options' => array('Size' => 'L', 'Color' => 'Red')
    	);
    	$this->cart->insert($data);
		if ($area == 'keranjang') {
			redirect('Member/keranjang');
		}
    	redirect('Member/janikLampung');
	}

	public function kurangKeranjang($rowid, $qty, $area = '')
	{
		$data = array(
	        'rowid' => $rowid,
	        'qty'   => ($qty-1)
	    );
	    var_dump($data);
		$this->cart->update($data);
		if ($area == 'keranjang') {
			redirect('Member/keranjang');
		}
    	redirect('Member/janikLampung');
	}
	public function bersihkanKeranjang($area = '')
	{
		$this->cart->destroy();
		if ($area == 'keranjang') {
			redirect('Member/keranjang');
		}
    	redirect('Member/janikLampung');
	}

	public function hapusItem($rowid, $area = '')
	{
		$this->cart->remove($rowid);
		if ($area == 'keranjang') {
			redirect('Member/keranjang');
		}
    	redirect('Member/janikLampung');
	}

	public function checkout()
	{
		$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
		$kode_bayar = strtoupper(bin2hex(random_bytes(16)));
		$data = array(
			'id_user' => $data['user']['id'],
			'kode_bayar' => $kode_bayar,
			'nama_penerima' => $this->input->post('nama_penerima'),
			'no_hp_penerima' => $this->input->post('no_hp_penerima'),
			'alamat_penerima' => $this->input->post('alamat_penerima'),
			'id_kurir' => $this->input->post('id_kurir'),
			'total_harga' => $this->input->post('total_harga'),
			// 'total_harga' => $this->input->post('total_harga'),
			'id_metode_bayar' => $this->input->post('id_metode_bayar'),
			'waktu_pemesanan' => date("Y-m-d H:i:s"),
			'Status' => 'Belum dibayar',
		);
		$this->db->insert('checkout', $data);
		$checkout = $this->db->get_where('checkout', ['kode_bayar' => $kode_bayar])->row_array();
		foreach ($this->cart->contents() as $item) {
			$kode_pesanan = strtoupper(bin2hex(random_bytes(16)));
			$data = array(
				'id_checkout' => $checkout['id_checkout'],
				'id_produk' => $item['id'],
				'kode_pesanan' => $kode_pesanan,
				'jumlah' => $item['qty'],
				'sub_total_harga' => $item['subtotal']
			);
			$this->db->insert('pesanan', $data);
			$produk = $this->db->get_where('produk',['id_produk' => $item['id']])->row_array();
			$new_stok = $produk['stok'] - $item['qty'];
			$this->db->where('id_produk', $item['id']);
			$this->db->update('produk',['stok' => $new_stok]);
		}
		$this->cart->destroy();
		$this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
			Pemesanan Anda berhasil! Silahkan mengisi Form Bukti Pembayaran
			</div>');
		redirect("Member/pembayaran/$kode_bayar");
	}

	public function keranjang()
	{
		$data['title'] = "Keranjang";
		$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
		$data['kurir'] = $this->db->get('kurir')->result_array();
		$this->load->view('layouts/header', $data);
		$this->load->view('layouts/sidebar', $data);
		$this->load->view('member/keranjang', $data);
		$this->load->view('layouts/footer');
	}

	public function pesanan()
	{
		$data['title'] = "Pesanan Saya";
		$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
		$this->db->select('*, checkout.id_checkout AS idc');
		$this->db->join('metode_bayar', 'checkout.id_metode_bayar = metode_bayar.id_metode_bayar');
		$data['checkout'] = $this->db->get_where('checkout', ['id_user' => $data['user']['id']])->result_array();
		$this->load->view('layouts/header', $data);
		$this->load->view('layouts/sidebar', $data);
		$this->load->view('member/pesanan-saya', $data);
		$this->load->view('layouts/footer');
	}

	public function detailPesanan($id_checkout)
	{
		$data['title'] = "Detail Pesanan";
		$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
		$this->db->select('*, checkout.id_checkout AS idc');
		$this->db->join('metode_bayar', 'checkout.id_metode_bayar = metode_bayar.id_metode_bayar');
		$this->db->join('kurir', 'checkout.id_kurir = kurir.id_kurir');
		$this->db->join('user', 'checkout.id_user = user.id');
		$data['checkout'] = $this->db->get_where('checkout', ['checkout.id_checkout' => $id_checkout])->row_array();

		$this->db->join('produk', 'produk.id_produk = pesanan.id_produk');
		$data['pesanan'] = $this->db->get_where('pesanan', ['id_checkout' => $id_checkout])->result_array();
		
		$this->db->select('*, bukti_transfer.id_bukti_transfer AS idbt, bukti_transfer.status AS sbt');
		$this->db->join('checkout', 'bukti_transfer.id_checkout = checkout.id_checkout');
		$this->db->join('user', 'checkout.id_user = user.id');
		$this->db->join('rekening', 'bukti_transfer.id_rekening_tujuan = rekening.id_rekening');
		$data['bukti_transfer'] = $this->db->get_where('bukti_transfer',['checkout.id_checkout' => $id_checkout])->result_array();

		$this->load->view('layouts/header', $data);
		$this->load->view('layouts/sidebar', $data);
		$this->load->view('member/detail-pesanan', $data);
		$this->load->view('layouts/footer');
	}

	public function updateStatusPesanan($id_checkout, $status = '')
	{
		$this->db->where('id_checkout', $id_checkout);
		$this->db->update('checkout', ['status' => 'Pesanan '.$status]);
		if ($status == 'dibatalkan') {
			$pesanan = $this->db->get_where('pesanan', ['id_checkout' => $id_checkout])->result_array();
			foreach ($pesanan as $row) {
				$produk = $this->db->get_where('produk',['id_produk' => $row['id_produk']])->row_array();
				$new_stok = $produk['stok'] + $row['jumlah'];
				$this->db->where('id_produk', $row['id_produk']);
				$this->db->update('produk',['stok' => $new_stok]);
			}
		}
		$this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
			Pesanan telah '.$status.'
			</div>');
		redirect('Member/pesanan');
	}

	public function pembayaran($kode_bayar = '', $nominal = 0)
	{
		$data['title'] = "Pembayaran";
		$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
		$data['rekening_tujuan'] = $this->db->get('rekening')->result_array();
		$data['kode_bayar'] = $kode_bayar;
		$data['nominal'] = $nominal;
		$data['form'] = 'uploadBuktiTransfer';
		$this->load->view('layouts/header', $data);
		$this->load->view('layouts/sidebar', $data);
		$this->load->view('member/pembayaran', $data);
		$this->load->view('layouts/footer');
	}

	public function uploadBuktiTransfer()
	{
		$checkout = $this->db->get_where('checkout', ['kode_bayar' => $this->input->post('kode_bayar')])->row_array();
		$upload_image = $_FILES['bukti_pembayaran']['name'];
		if ($upload_image) {
			$config['allowed_types'] = 'gif|jpg|png|svg|jpeg';
			$config['upload_path'] = './assets/img/bukti_pembayaran';
			$config['max_size']     = '2048';
			$this->load->library('upload', $config);
			if ($this->upload->do_upload('bukti_pembayaran')) {
				$new_image = $this->upload->data('file_name');
				$data = array(
					'id_checkout' => $checkout['id_checkout'],
					'id_rekening_tujuan' => $this->input->post('id_rekening_tujuan'),
					'rekening_pengirim' => $this->input->post('rekening_pengirim'),
					'bank_pengirim' => $this->input->post('bank_pengirim'),
					'nama_pengirim' => $this->input->post('nama_pengirim'),
					'waktu_transfer' => $this->input->post('tanggal_transfer').' '.$this->input->post('waktu_transfer'),
					'nominal_transfer' => $this->input->post('nominal_transfer'),
					'bukti_pembayaran' => $new_image,
					'catatan' => $this->input->post('catatan'),
					'status' => 'Belum dikonfirmasi',
				);
				$this->db->insert('bukti_transfer', $data);

				$this->db->where('id_checkout', $checkout['id_checkout']);
				$this->db->update('checkout', ['status' => 'Menunggu konfirmasi pembayaran']);
				$this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
					Bukti Pembayaran Terkirim
					</div>');
				redirect('Member/pesanan');
			} else{
				$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">'.$this->upload->display_errors().'</div>');
				redirect('Member/pembayaran');
			}
		} else{
			$this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
				Bukti Pembayaran Wajib diupload
				</div>');
			redirect('Member/pembayaran');
		}
	}

	public function riwayatPembayaran($id_checkout = '')
	{
		$data['title'] = "Riwayat Pembayaran";
		$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
		if ($id_checkout) {
			$where = ['id_user' => $data['user']['id'], 'id_checkout' => $id_checkout];
		} else{
			$where = ['id_user' => $data['user']['id']];
		}
		$this->db->select('*, bukti_transfer.id_bukti_transfer AS idbt, bukti_transfer.status AS sbt');
		$this->db->join('checkout', 'bukti_transfer.id_checkout = checkout.id_checkout');
		$this->db->join('user', 'checkout.id_user = user.id');
		$this->db->join('rekening', 'bukti_transfer.id_rekening_tujuan = rekening.id_rekening');
		$data['bukti_transfer'] = $this->db->get_where('bukti_transfer',$where)->result_array();
		$this->load->view('layouts/header', $data);
		$this->load->view('layouts/sidebar', $data);
		$this->load->view('member/riwayat-pembayaran', $data);
		$this->load->view('layouts/footer');
	}

	public function online()
	{
		redirect('Member/pesanan');
	}
}
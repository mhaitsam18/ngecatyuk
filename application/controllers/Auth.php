<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Auth extends CI_Controller
{

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/userguide3/general/urls.html
	 */
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Google_login_model');
		date_default_timezone_set('Asia/Jakarta');
	}

	public function index() //API LOGIN BY GOOGLE
	{
		if ($this->session->userdata('username')) {
			redirect('user');
		}
		require "vendor/autoload.php"; //KITA AMBIL GOOGLE KLIEN DARI APICLIENT YANG DIDOWNLOAD DENGAN COMPOSER
		$google_client = new Google_Client(); //KITA BIKIN OBJEK UNTUK MENDEKLARASIKAN GOOGLE-API DARI AUTOLOAD.PHP

		// $google_client = new Google\Client();

		$google_client->setClientId('892439740462-4976rses6q7e4rjf10l2065rk40m67n2.apps.googleusercontent.com'); //DEFINISIKAN CLIENT ID YANG KITA DAPAT DARI CONSOLE, DAN TARUH DI SINI

		$google_client->setClientSecret('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'); //DEFINISIKAN CLIENT SECRET YANG KITA DAPAT DARI CONSOLE, DAN TARUH DI SINI

		$google_client->setRedirectUri(base_url() . 'auth'); //KALO KITA BERHASIL LOGIN, MAKA PINDAHKAN KEMANA?

		$google_client->addScope('email'); //AMBIL DATA EMAIL
		$google_client->setScopes(array('email', 'https://www.googleapis.com/auth/plus.login')); //AMBIL DATA EMAIL

		$google_client->addScope('profile'); // AMBIL DATA PROFIL SEPERTI GENDER, TANGGAL LAHIR DSB.


		if (isset($_GET["code"])) { //INI KODE DARI TOMBOL LOGIN BY GOOGLE
			$token = $google_client->fetchAccessTokenWithAuthCode($_GET["code"]); //INI UNTUK MENGECEK TOKEN DARI TOMBOL GOOGLE NYA

			if (!isset($token["error"])) { // KALO TOKEN NYA ERROR, NANTI GAGAL LOGIN
				$google_client->setAccessToken($token['access_token']); //SETELAH TOKENNYA DINATAKAN VALID, GUNAKAN TOKEN UNTUK MENDAPATKAN DATA

				$this->session->set_userdata('access_token', $token['access_token']); //SIMPAN TOKENNYA KE DALAM SEBUAH SESSION

				$google_service = new Google\Service\Oauth2($google_client); // GUNAKAN LIBRARY GOOGLE SERVIS UNTUK MENGAMBIL DATA KE DALAM SEBUAH OJEK

				$data = $google_service->userinfo->get(); //AMBIL DATA-DATA, KEMUDIAN MASUKKAN KE DALAM VARIABEL $data DALAM BENTUK ARRAY
				// var_dump($data);
				// die;

				if ($this->Google_login_model->Is_already_register($data['id']) || $this->Google_login_model->Is_email_register($data['email'])) { //LAKUKAN PENGECEKAN, AOAKAH DATA YANG DIAMBIL DARI GOOGLE SUDAH TERDAFTAR DI APLIKASI ATAU BELUM?


					//JIKA SUDAH, MAKA DATA AKAN TERUPDATE
					//update data
					$user_data = array(
						'login_oauth_uid' => $data['id'],
						'email' => $data['email'],
						'username' => str_replace('@gmail.com', '', $data['email']),
						'google_image' => $data['picture'],
						'is_active' => 1,
					);

					$this->Google_login_model->Update_user_data($user_data, $data['id']);



					$user = $this->db->get_where('user', ['username' => $user_data['username']])->row_array();

					//SETELAH UPDATE DATA, DATA-DATA LOGIN AKAN TERSIMPAN KE DALAM SESSION,
					$login = [
						'id' => $user['id'],
						'username' => $user['username'],
						'email' => $user['email'],
						'role_id' => $user['role_id'],
					];
					$this->session->set_userdata($login);

					//KEMUDIAN LOG IN DAN MASUK KE DALAM APLIKASI
					if ($user['role_id' == 1]) {
						redirect('admin');
					} else {
						redirect('user');
					}
				} else {
					//insert data
					//JIKA DATA-DATA DARI GOOGLE BELUM TERDAFTAR DI APLIKASI, MAKA DATA-DATA DARI GOOGLE AKAN TERSIMPAN DI DALAM DATABASE APLIKASI NYA
					$user_data = array(
						'login_oauth_uid' => $data['id'],
						'name'  => $data['given_name'] . ' ' . $data['family_name'],
						'email'  => $data['email'],
						'username' => str_replace('@gmail.com', '', $data['email']),
						'google_image' => $data['picture'],
						'role_id' => 2,
						'is_active' => 1,
						'religion_id' => 1,
						'birthday' => date('Y-m-d'),
						'image' => 'default.png',
						'date_created'  => time()
					);

					$this->Google_login_model->Insert_user_data($user_data); //INI ADALAH PROSES PENYIMPANAN DATA-DATA DARI GOOGLE NYAA

					$user = $this->db->get_where('user', ['username' => $user_data['username']])->row_array();

					//DATA-DATA YANG BARU SAJA TERSIMPAN, AKAN MASUK KE DALAM SESSION, AGAR BISA LOG IN
					$login = [
						'id' => $user['id'],
						'username' => $user['username'],
						'email' => $user['email'],
						'role_id' => $user['role_id']
					];
					$this->session->set_userdata($login);

					//HALAMAN AKAN BERPINDAH KE HALAMAN ADMIN ATAU USER
					if ($user['role_id' == 1]) {
						redirect('admin');
					} else {
						redirect('user');
					}
				}
				$this->session->set_userdata('user_data', $user_data);

				// TAMAT
			}
		}

		//INI ADALAH SEBUAH KONDISI JIKA USER BELUM MELAKUKAN LOG IN
		$this->form_validation->set_rules('username', 'username', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');
		if ($this->form_validation->run() == false) {
			$data['google_client'] = $google_client;
			$data['title'] = 'User Log In';
			$data['page'] = 'auth/login';
			$this->load->view('layouts/auth_main', $data);
		} else {
			$this->_login();
		}
	}

	public function registration()
	{
		if ($this->session->userdata('username')) {
			redirect('user');
		}
		$this->db->where_not_in('id', [0, 1, 3, 4]);
		$data['user_role'] = $this->db->get('user_role')->result_array();
		$this->form_validation->set_rules('name', 'Name', 'required|trim');
		$this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]', [
			'is_unique' => 'this email has already registered!'
		]);
		$this->form_validation->set_rules('username', 'Username', 'required|trim|is_unique[user.username]', [
			'is_unique' => 'this Username has already registered!'
		]);
		$this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]', [
			'matches' => 'password dont match!',
			'min_length' => 'password too short!'
		]);
		// $this->form_validation->set_rules('role_id','Role', 'required|trim');
		$this->form_validation->set_rules('gender', 'Gander', 'required|trim');
		$this->form_validation->set_rules('place_of_birth', 'Place of Birth', 'required|trim');
		$this->form_validation->set_rules('birthday', 'Birth Day', 'required|trim');
		$this->form_validation->set_rules('phone_number', 'Phone Number', 'required|trim');
		$this->form_validation->set_rules('address', 'Address', 'required|trim');
		$this->form_validation->set_rules('religion_id', 'Religion', 'required|trim');
		// 
		$this->form_validation->set_rules('password2', 'Confrim Password', 'required|trim|matches[password1]');
		$data['agama'] = $this->db->get('agama')->result_array();
		if ($this->form_validation->run() == false) {
			$data['title'] = 'User Registration';
			$data['page'] = 'auth/registration';
			$this->load->view('layouts/auth_main', $data);
		} else {
			$data = [
				'name' => htmlspecialchars($this->input->post('name', true)),
				'email' => htmlspecialchars($this->input->post('email', true)),
				'username' => htmlspecialchars($this->input->post('username', true)),
				'gender' => htmlspecialchars($this->input->post('gender', true)),
				'place_of_birth' => htmlspecialchars($this->input->post('place_of_birth', true)),
				'birthday' => htmlspecialchars($this->input->post('birthday', true)),
				'phone_number' => htmlspecialchars($this->input->post('phone_number', true)),
				'address' => htmlspecialchars($this->input->post('address', true)),
				'religion_id' => htmlspecialchars($this->input->post('religion_id', true)),
				'image' => 'default.svg',
				'password' => password_hash($this->input->post('password1'), PASSWORD_DEFAULT),
				// 'role_id' => htmlspecialchars($this->input->post('role_id', true)),
				'role_id' => 2,
				'is_active' => 1,
				// 'is_active' => 0,
				'date_created' => time(),
			];

			$token = base64_encode(random_bytes(32));
			$user_token = [
				'email' => $this->input->post('email', true),
				'token' => $token,
				'date_created' => time()
			];

			$this->db->insert('user', $data);
			$this->db->insert('user_token', $user_token);

			// $this->_sendEmail($token, 'verify');

			$user = $this->db->get_where('user', ['email' => $this->input->post('email', true)])->row_array();

			$this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
				Congratulation! Your account has been created, check your email! please activate!
				</div>');
			redirect('auth');
		}
	}

	private function _sendEmail($token, $type)
	{
		$config = [
			'protocol' => 'smtp',
			'smtp_host' => 'ssl://smtp.googlemail.com',
			'smtp_user' => 'hafandrostudio@gmail.com',
			'smtp_pass' => 'Anggun29',
			'smtp_port' => 465,
			'mailtype' => 'html',
			'chatset' => 'utf-8',
			'newline' => "\r\n"
		];

		$this->load->library('email', $config);
		$this->email->initialize($config);
		$this->email->from('hafandrostudio@gmail.com', 'HAFA Studio');
		$this->email->to($this->input->post('email'));
		if ($type == 'verify') {
			$this->email->subject('Account Verification');
			$this->email->message('Click this link to verify your account : <a href="' . base_url('auth/verify?email=') . $this->input->post('email') . '&token=' . urlencode($token) . '">Activate</a>');
		} elseif ($type == 'forgot') {
			$this->email->subject('Reset Password');
			$this->email->message('Click this link to reset your password : <a href="' . base_url('auth/resetPassword?email=') . $this->input->post('email') . '&token=' . urlencode($token) . '">Reset Password</a>');
		}
		if ($this->email->send()) {
			return true;
		} else {
			echo $this->email->print_debugger();
			die;
		}
	}

	public function verify()
	{
		$email = $this->input->get('email');
		$token = $this->input->get('token');

		$user = $this->db->get_where('user', ['email' => $email])->row_array();
		if ($user) {
			if ($user['is_active'] != 1) {
				$user_token = $this->db->get_where('user_token', ['token' => $token])->row_array();
				if ($user_token) {
					if (time() - $user_token['date_created'] < (60 * 60 * 24)) {
						$this->db->set('is_active', 1);
						$this->db->where('email', $email);
						$this->db->update('user');
						$this->db->delete('user_token', ['email' => $email]);
						$this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
						' . $email . ' has been activated. Please Login! Please Check Your Email!
						</div>');
						redirect('auth');
					} else {
						$this->db->delete('user', ['email' => $email]);
						$this->db->delete('user_token', ['email' => $email]);
						$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
						Account activation failed: Token Expired!
						</div>');
						redirect('auth');
					}
				} else {
					$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
					Account activation failed: Invalid Token!
					</div>');
					redirect('auth');
				}
			} else {
				$this->session->set_flashdata('message', '<div class="alert alert-warning" role="alert">
				Your account has been activated!
				</div>');
				redirect('auth');
			}
		} else {
			$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
			Account activation failed: Wrong Email!
			</div>');
			redirect('auth');
		}
	}

	private function _login()
	{
		if ($this->session->userdata('username')) {
			redirect('user');
		}
		$username = $this->input->post('username');
		$password = $this->input->post('password');

		$user = $this->db->get_where('user', ['username' => $username])->row_array();
		if ($user) {
			if ($user['is_active'] ==  1) {
				if (password_verify($password, $user['password'])) {
					$data = [
						'id' => $user['id'],
						'username' => $user['username'],
						'email' => $user['email'],
						'role_id' => $user['role_id']
					];
					$this->session->set_userdata($data);
					if ($user['role_id' == 1]) {
						redirect('admin');
					} else {
						redirect('user');
					}
				} else {
					$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
						Wrong password!
						</div>');
					redirect('auth');
				}
			} else {
				$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
					This email has not been activated! Please Check Your Email!
					</div>');
				redirect('auth');
			}
		} else {
			$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
				Email is not registered!
				</div>');
			redirect('auth');
		}
	}

	public function logout()
	{
		$this->session->unset_userdata('id');
		$this->session->unset_userdata('username');
		$this->session->unset_userdata('email');
		$this->session->unset_userdata('role_id');
		$this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
			You have been log out
			</div>');
		redirect('auth');
	}

	public function blocked()
	{
		$this->load->view('auth/blocked');
	}

	public function notFound()
	{
		$data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
		$data['title'] = "Error 404 Page not Found";
		$this->load->view('auth/page-not-found', $data);
	}

	public function forgotPassword()
	{
		if ($this->session->userdata('username')) {
			redirect('user');
		}
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		if ($this->form_validation->run() == false) {
			$data['title'] = 'Forgot Password';
			$data['page'] = 'auth/forgot-password';
			$this->load->view('layouts/auth_main', $data);
		} else {
			$email = $this->input->post('email');
			$user = $this->db->get_where('user', ['email' => $email, 'is_active' => 1])->row_array();
			if ($user) {
				$token = base64_encode(random_bytes(32));
				$user_token = [
					'email' => $email,
					'token' => $token,
					'date_created' => time()
				];
				$this->db->insert('user_token', $user_token);
				$this->_sendEmail($token, 'forgot');
				$this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
					Please check your email to reset password!
					</div>');
				redirect('auth/forgotPassword');
			} else {
				$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
					Email is not registered!
					</div>');
				redirect('auth/forgotPassword');
			}
		}
	}

	public function resetPassword()
	{
		$email = $this->input->get('email');
		$token = $this->input->get('token');

		$user = $this->db->get_where('user', ['email' => $email])->row_array();
		if ($user) {
			$user_token = $this->db->get_where('user_token', ['token' => $token])->row_array();
			if ($user_token) {
				if (time() - $user_token['date_created'] < (60 * 60 * 24)) {
					$this->session->set_userdata('reset_email', $email);
					$this->changePassword();
				} else {
					$this->db->delete('user_token', ['email' => $email]);
					$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
					Reset Password failed: Token Expired!
					</div>');
					redirect('auth');
				}
			} else {
				$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
				Reset Password failed: Invalid Token!
				</div>');
				redirect('auth');
			}
		} else {
			$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
			Reset Password failed: Wrong email!
			</div>');
			redirect('auth');
		}
	}

	public function changePassword()
	{
		if (!$this->session->userdata('reset_email')) {
			redirect('auth');
		}
		$this->form_validation->set_rules('password1', 'Password', 'required|trim|min_length[3]');
		$this->form_validation->set_rules('password2', 'Confrim Password', 'required|trim|matches[password1]');
		if ($this->form_validation->run() == false) {
			$data['title'] = 'change Password';
			$data['page'] = 'auth/change-password';
			$this->load->view('layouts/auth_main', $data);
		} else {
			$password = password_hash($this->input->post('password1'), PASSWORD_DEFAULT);
			$email = $this->session->userdata('reset_email');

			$this->db->set('password', $password);
			$this->db->where('email', $email);
			$this->db->update('user');
			$this->session->unset_userdata('reset_email');
			$this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">
			Password has been change! Please login!
			</div>');
			redirect('auth');
		}
	}

	public function forgotPassword2()
	{
		if ($this->session->userdata('username')) {
			redirect('user');
		}
		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		if ($this->form_validation->run() == false) {
			$data['title'] = 'Forgot Password';
			$data['page'] = 'auth/forgot-password-2';
			$this->load->view('layouts/auth_main', $data);
		} else {
			$email = $this->input->post('email');
			$user = $this->db->get_where('user', ['email' => $email, 'is_active' => 1])->row_array();
			if ($user) {
				$pertanyaan_keamanan = $this->db->get_where('pertanyaan_keamanan', ['id_user' => $user['id']])->row_array();
				if ($pertanyaan_keamanan) {
					redirect('auth/question/' . $pertanyaan_keamanan['id']);
				} else {
					$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
						Sorry, Your account does not have a security question!
						</div>');
					redirect('auth/forgotPassword2');
				}
			} else {
				$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
					Email is not registered or not verified!
					</div>');
				redirect('auth/forgotPassword2');
			}
		}
	}

	public function question($id = '')
	{
		$this->db->select('*, pertanyaan_1.pertanyaan AS p1, pertanyaan_2.pertanyaan AS p2, pertanyaan_keamanan.id AS pid');
		$this->db->join('pertanyaan_1', 'pertanyaan_1.id = pertanyaan_keamanan.id_pertanyaan_1');
		$this->db->join('pertanyaan_2', 'pertanyaan_2.id = pertanyaan_keamanan.id_pertanyaan_2');
		$data['pertanyaan_keamanan'] = $this->db->get_where('pertanyaan_keamanan', ['pertanyaan_keamanan.id' => $id])->row_array();
		$data['user'] = $this->db->get_where('user', ['id' => $data['pertanyaan_keamanan']['id_user']])->row_array();
		$this->form_validation->set_rules('jawaban_1', 'Answer 1', 'trim|required');
		$this->form_validation->set_rules('jawaban_2', 'Answer 2', 'trim|required');
		if ($this->form_validation->run() == false) {
			$data['title'] = 'Forgot Password';
			$data['page'] = 'auth/question';
			$this->load->view('layouts/auth_main', $data);
		} else {
			$check_answer = $this->db->get_where('pertanyaan_keamanan', [
				'id' => $id,
				'jawaban_1' => $this->input->post('jawaban_1'),
				'jawaban_2' => $this->input->post('jawaban_2'),
			])->num_rows();
			if ($check_answer > 0) {
				$this->session->set_userdata('reset_email', $data['user']['email']);
				redirect('auth/changePassword');
			} else {
				$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert">
					Sorry, your answer did not match, please try again!
					</div>');
				redirect('auth/question/' . $id);
			}
		}
	}
}

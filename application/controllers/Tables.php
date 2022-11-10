<?php
defined('BASEPATH') or exit('No direct script access allowed');

include_once(__DIR__ . '/Admin.php');
class Tables extends CI_Controller
{
	public $simple = 'fungitu2_Simple';

	public function __construct()
	{
		parent::__construct();
	}

	public function index($table_name)
	{
		if ($this->input->method() == 'options') {
			$this->output->set_content_type('application/json', 'utf-8')
				->set_output('post')
				->_display();
			die();
		}
		if ($this->input->method() != 'post') {
			$this->output->set_status_header(405);
			die();
		}
		//Listeleme yetki kontrolü 
		$auths = $this->auths($table_name, 'list');

		//model bağlantısı
		$this->load->model('table_model');
		$this->table_model->tableName = $table_name;

		//gelen params'ın parçalanması
		$body = json_decode($this->input->raw_input_stream);
		$filter = $body->filter ?? array();
		$like = $body->like ?? array();
		$order = "";
		if (!empty($body->order->name) && !empty($body->order->type)) {
			$order = $body->order->name . " " . $body->order->type;
		}
		$limit = $body->limit ?? 10;
		$page = $body->page ?? 1;


		if ($auths['auths']->own_data == "1") {
			$filter['own_id'] = $auths['user']->id;
		}

		//veritabanından veri çekme
		$datas = $this->table_model->get_all($filter, $order, $limit, $page, $like);

		//kolonları çekme
		$columns = [];

		$columns = $this->auths_column($auths, 'list');



		$newData = [];

		foreach ($datas as $key => $value) {
			foreach ($columns as $key1 => $value1) {
				if ($value1['key'] == 'MUL' && !empty($value1['table_name'])) {
					$mul_data = (array) $this->get_in($value1['table_name'], $value->$key1);
					$newData[$key][$key1] = [
						'id' => $value->$key1,
						'data' => $mul_data[$value1['table_column']],
					];
				} else {
					$newData[$key][$key1] = $value->$key1;
				}
			}
		}




		$count = $this->table_model->count($filter, $like);
		//kullanılabilir json yapısı kurma
		$data = [
			"data" => $newData,
			"columns" => $columns,
			"page" => $page,
			"count" => $count,
			'auths' => $auths,
			"status" => "success"
		];

		//json yapısını döndürme
		header('Content-Type: application/json');
		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($data))
			->_display();
		die();
	}
	public function get_in($table_name, $id)
	{

		$this->load->model('table_model');

		$this->table_model->tableName = $table_name;

		$datas = $this->table_model->get(array("id" => $id));

		return $datas;
	}
	public function list($table_name, $column)
	{
		$this->table_model->tableName = $table_name;
		$datas = $this->table_model->get_all([], [], 1000);
		$newData = [];
		foreach ($datas as $key => $value) {
			$newData[$key] = [
				'id' => $value->id,
				'label' => $value->$column
			];
		}
		return $newData;
	}
	public function get($table_name, $id)
	{
		if ($this->input->method() == 'options') {
			$this->output->set_content_type('application/json', 'utf-8')
				->set_output('get')
				->_display();
			die();
		}
		if ($this->input->method() != 'get') {
			$this->output->set_status_header(405);
			die();
		}
		//Listeleme yetki kontrolü 
		$auths = $this->auths($table_name, 'list');

		$this->load->model('table_model');

		$this->table_model->tableName = $table_name;

		$datas = $this->table_model->get(array("id" => $id));
		//kolonları çekme

		$columns = $this->auths_column($auths, 'get');

		$newData = (object) [];


		foreach ($columns as $key1 => $value1) {
			if ($value1['key'] == 'MUL' && !empty($value1['table_name'])) {
				$mul_data = (array) $this->get_in($value1['table_name'], $datas->$key1);
				$newData->$key1 = [
					'id' => $datas->$key1,
					'data' => $mul_data[$value1['table_column']],
				];
			} else {
				$newData->$key1 =
					$datas->$key1;
			}
		}

		$data = [
			"data" => $newData,
			"columns" => $columns,
			"status" => "success"
		];

		header('Content-Type: application/json');
		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($data))
			->_display();
		die();
	}
	public function first($table_name)
	{
		if ($this->input->method() == 'options') {
			$this->output->set_content_type('application/json', 'utf-8')
				->set_output('get')
				->_display();
			die();
		}
		if ($this->input->method() != 'get') {
			$this->output->set_status_header(405);
			die();
		}
		//Listeleme yetki kontrolü 
		$auths = $this->auths($table_name, 'list');

		$this->load->model('table_model');

		$this->table_model->tableName = $table_name;

		//kolonları çekme
		if ($auths['auths_group_id'] == 0) {
			$columns = $this->table_model->columns($table_name);
		} else {
			$columns = $this->auths_column($auths, 'get');
		}
		$data = [
			"data" => $this->table_model->first(),
			"columns" => $columns,
			"status" => "success"
		];

		header('Content-Type: application/json');
		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($data))
			->_display();
		die();
	}
	public function last($table_name)
	{
		if ($this->input->method() == 'options') {
			$this->output->set_content_type('application/json', 'utf-8')
				->set_output('get')
				->_display();
			die();
		}
		if ($this->input->method() != 'get') {
			$this->output->set_status_header(405);
			die();
		}
		//Listeleme yetki kontrolü 
		$auths = $this->auths($table_name, 'list');

		$this->load->model('table_model');

		$this->table_model->tableName = $table_name;

		//kolonları çekme
		if ($auths['auths_group_id'] == 0) {
			$columns = $this->table_model->columns($table_name);
		} else {
			$columns = $this->auths_column($auths, 'get');
		}
		$data = [
			"data" => $this->table_model->last($table_name),
			"columns" => $columns,
			"status" => "success"
		];

		header('Content-Type: application/json');
		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($data))
			->_display();
		die();
	}
	public function count($table_name)
	{
		if ($this->input->method() == 'options') {
			$this->output->set_content_type('application/json', 'utf-8')
				->set_output('post')
				->_display();
			die();
		}
		if ($this->input->method() != 'post') {
			$this->output->set_status_header(405);
			die();
		}
		//model bağlantısı
		$this->load->model('table_model');

		$this->table_model->tableName = $table_name;

		//gelen params'ın parçalanması
		$body = json_decode($this->input->raw_input_stream);
		$filter = $body->filter ?? array();
		$like = $body->like ?? array();

		//veritabanından veri çekme
		$datas = $this->table_model->count($filter, $like);

		$data = [
			"data" => $datas,
			"status" => "success"
		];

		header('Content-Type: application/json');
		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($data))
			->_display();
		die();
	}
	public function create($table_name)
	{
		if ($this->input->method() == 'options') {
			$this->output->set_content_type('application/json', 'utf-8')
				->set_output('get')
				->_display();
			die();
		}
		if ($this->input->method() != 'get') {
			$this->output->set_status_header(405);
			die();
		}
		$auths = $this->auths($table_name, 'create');
		$this->load->model('table_model');

		//kolonları çekme
		$columns = $this->auths_column($auths, 'create');


		foreach ($columns as $key => $value) {
			if ($value['key'] == 'MUL' && !empty($value['table_name'])) {
				$columns[$key]['list'] =
					(array) $this->list($value['table_name'], $value['table_column']);
			}
		}

		$data = [
			"columns" => $columns,
			"status" => "success"
		];

		header('Content-Type: application/json');
		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($data))
			->_display();
		die();
	}
	public function store($table_name)
	{

		if ($this->input->method() == 'options') {
			$this->output->set_content_type('application/json', 'utf-8')
				->set_output('put')
				->_display();
			die();
		}
		if ($this->input->method() != 'put') {
			$this->output->set_status_header(405);
			die();
		}
		//Ekleme yetki kontrolü 
		$auths = $this->auths($table_name, 'create');



		$this->load->model('table_model');

		$this->table_model->tableName = $table_name;
		header('Content-Type: application/json');

		if ($auths['auths_group_id'] == 0) {
			$columns = $this->table_model->columns($table_name);
		} else {
			$columns = $this->auths_column($auths, 'create');
		}
		$post = $this->input->post();
		$get = (array) json_decode($this->input->raw_input_stream);
		$input = array();
		if (empty($post) == 1) {
			$input = $get;
		} else {
			$input = $post;
		}

		foreach ($columns as $key => $value) {
			if ($key == 'password') {
				if (!empty($value)) {
					$parse = md5($input[$key]);
					$input[$key] = $parse;
				}
			}
		}

		$input['status'] = 1;
		$input['own_id'] = $auths['user']->id;
		$input['user_id'] = $auths['user']->id;
		$input['added_date'] = date("Y-m-d H:i:s");
		$input['updated_date'] = date("Y-m-d H:i:s");


		$ret = $this->table_model->add($input, $table_name);


		if ($ret && $table_name == 'mail') {
			$data['mail_status'] = $this->email_send($input['users'], $input['title'], $input['message']);
		}
		if ($ret && $table_name == 'tables') {

			$this->load->model('admin_model');
			$state = 1;
			$state = $this->admin_model->createTable([
				'name' => $input['name'],
				'display' => $input['display']
			]);
			$data = [
				'name' => $input['name'] . ' -> admin',
				'table_name' => $input['name'],
				'auths_group_id' => "1",
				'own_data' => 0,
				'list_access' => 1,
				'create_access' => 1,
				'edit_access' => 1,
				'delete_access' => 1,
				'list_hide' => '',
				'create_hide' => 'id,own_id,user_id,added_date,updated_date',
				'edit_hide' => 'id,own_id,user_id,added_date,updated_date',
				'status' => 1,
				'own_id' => $auths['user']->id,
				'user_id' => $auths['user']->id,
				'added_date' => date("Y-m-d H:i:s"),
				'updated_date' => date("Y-m-d H:i:s")
			];

			$this->table_model->__set('tableName', 'auths');
			$data['table_status'] = $this->table_model->add($data, 'auths');
		}

		$data = [
			"status" => $ret ? "success" : "error",
			"last" => $this->table_model->last($table_name),
			"table_name" => $table_name
		];
		if ($ret) {
			header('Content-Type: application/json');
			$this->output
				->set_content_type('application/json', 'utf-8')
				->set_output(json_encode($data))
				->_display();
			die();
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Kayıt sırasında bir hata ile karşılaşıldı.', 'data' => $ret]);
			$this->output->set_status_header(400);
			die();
		}
	}
	public function delete($table_name, $id)
	{
		if ($this->input->method() == 'options') {
			$this->output->set_content_type('application/json', 'utf-8')
				->set_output('delete')
				->_display();
			die();
		}
		if ($this->input->method() != 'delete') {
			$this->output->set_status_header(405);
			die();
		}
		//Silme yetki kontrolü 
		$auths = $this->auths($table_name, 'delete');

		$this->load->model('table_model');

		$this->table_model->tableName = $table_name;


		$where = ['id' => $id];

		if ($table_name == 'tables') {
			$this->table_model->tableName = 'tables';
			$old_data = $this->table_model->get(array("id" => $id));

			$this->load->model('admin_model');
			$this->admin_model->deleteTable($old_data->name);
		}

		//$ret = $this->table_model->delete($where);
		$ret = $this->table_model->update($where, ['status' => 0]);
		$data = [
			"status" => $ret ? "success" : "error",
		];
		if ($ret) {
			header('Content-Type: application/json');
			$this->output
				->set_content_type('application/json', 'utf-8')
				->set_output(json_encode($data))
				->_display();
			die();
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Kayıt sırasında bir hata ile karşılaşıldı.', 'data' => $ret]);
			$this->output->set_status_header(400);
			die();
		}
	}
	public function edit($table_name, $id)
	{
		if ($this->input->method() == 'options') {
			$this->output->set_content_type('application/json', 'utf-8')
				->set_output('get')
				->_display();
			die();
		}
		if ($this->input->method() != 'get') {
			$this->output->set_status_header(405);
			die();
		}
		//Listeleme yetki kontrolü 
		$auths = $this->auths($table_name, 'edit');

		$this->load->model('table_model');

		$this->table_model->tableName = $table_name;

		//kolonları çekme

		$columns = $this->auths_column($auths, 'edit');
		$datas = $this->table_model->get(array("id" => $id));
		$newData = (array)[];
		foreach ($columns as $key => $value) {
			$newData[$key] =
				$datas->$key;
			if ($value['key'] == 'MUL' && !empty($value['table_name'])) {
				$columns[$key]['list'] =
					(array) $this->list($value['table_name'], $value['table_column']);
			}
		}
		$data = [
			"data" => $newData,
			"columns" => $columns,
			"status" => "success"
		];

		header('Content-Type: application/json');
		$this->output
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode($data))
			->_display();
		die();
	}
	public function update($table_name, $id)
	{
		if ($this->input->method() == 'options') {
			$this->output->set_content_type('application/json', 'utf-8')
				->set_output('patch')
				->_display();
			die();
		}
		if ($this->input->method() != 'patch') {
			$this->output->set_status_header(405);
			die();
		}
		//Listeleme yetki kontrolü 
		$auths = $this->auths($table_name, 'edit');

		$columns = $this->auths_column($auths, 'create');

		$this->load->model('table_model');



		$post = $this->input->post();
		$get = (array) json_decode($this->input->raw_input_stream);
		$input = array();
		if (empty($post) == 1) {
			$input = $get;
		} else {
			$input = $post;
		}
		$input['user_id'] = $auths['user']->id;
		$input['updated_date'] = date("Y-m-d H:i:s");

		foreach ($columns as $key => $value) {
			if ($key == 'password') {
				if (!empty($input[$key])) {
					$parse = md5($input[$key]);
					$input[$key] = $parse;
				}
			}
		}



		if ($auths['auths_group_id'] == 0) {
			$columns = $this->table_model->columns($table_name);
		} else {
			$columns = $this->table_model->columns($table_name);
		}
		$where = ['id' => $id];






		if ($table_name == 'tables') {
			$this->table_model->tableName = 'tables';
			$old_data = $this->table_model->get(array("id" => $id));

			$this->load->model('admin_model');
			if (empty($input['name'])) {
				$input['name'] = $old_data['name'];
			}
			$state = 1;
			$state = $this->admin_model->updateTable([
				'old_name' => $old_data->name,
				'old_display' => $old_data->display,
				'name' => $input['name'],
				'display' => $input['display']
			]);
		}
		$this->table_model->tableName = $table_name;
		$ret = $this->table_model->update($where, $input);

		$data = [
			"status" => $ret ? "success" : "error",
			"data" => $this->table_model->get(['id' => $id]),

			'updated' => $input
		];
		if ($ret) {
			header('Content-Type: application/json');
			$this->output
				->set_content_type('application/json', 'utf-8')
				->set_output(json_encode($data))
				->_display();
			die();
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Kayıt sırasında bir hata ile karşılaşıldı.', 'data' => $ret]);
			$this->output->set_status_header(400);
			die();
		}
	}
	public function auths($table_name, $operation)
	{

		$this->load->model('table_model');

		$this->table_model->tableName = 'users';

		//headerden token alma
		$_Header = $this->input->request_headers();
		$token = $_Header['token'] ?? $_Header['Token'];

		if (empty($token) || $token == 'null') $token = '0';

		//kullanıcı kontrolü
		$user = $this->table_model->get(['token' => $token]);

		$auths_group_id = $this->table_model->get(['token' => $token])->auths_group_id ?? 3;


		$this->table_model->tableName = 'auths';
		$auths = $this->table_model->get(['auths_group_id' => $auths_group_id, 'table_name' => $table_name]);

		if ($auths == null) {
			echo json_encode(['status' => 'error', 'message' => 'Tablo yetkisi yok']);
			$this->output->set_status_header(401);
			exit;
		}
		if ($operation == 'list') {
			if ($auths->list_access == 0) {
				echo json_encode(['status' => 'error', 'message' => 'Tablo listeleme yetkisi yok']);
				$this->output->set_status_header(401);
				exit;
			}
		} else if ($operation == 'create') {
			if ($auths->create_access == 0) {
				echo json_encode(['status' => 'error', 'message' => 'Tablo ekleme yetkisi yok']);
				exit;
			}
		} else if ($operation == 'edit') {
			if ($auths->edit_access == 0) {
				echo json_encode(['status' => 'error', 'message' => 'Tablo düzenleme yetkisi yok']);
				$this->output->set_status_header(401);
				exit;
			}
		} else if ($operation == 'delete') {
			if ($auths->delete_access == 0) {
				echo json_encode(['status' => 'error', 'message' => 'Tablo silme yetkisi yok']);
				$this->output->set_status_header(401);
				exit;
			}
		}

		return [
			'user' => $user,
			'auths_group_id' => $auths_group_id,
			'auths' => $auths
		];
	}
	public function auths_column($auths, $operation)
	{
		$columns = [];
		$this->load->model('table_model');



		if ($operation == 'list') {
			$columns = [];
			$columns = $this->table_model->columns($auths['auths']->table_name);
			if ($auths['auths']->list_hide != null) {

				foreach ($columns as $key => $value) {
					if (array_search($key, explode(',', $auths['auths']->list_hide)) > -1) {
						unset($columns[$key]);
					}
				}
			}
		} else if ($operation == 'get') {
			$columns = [];
			$columns = $this->table_model->columns($auths['auths']->table_name);
			if ($auths['auths']->get_hide != null) {

				foreach ($columns as $key => $value) {
					if (array_search($key, explode(',', $auths['auths']->get_hide)) > -1) {
						unset($columns[$key]);
					}
				}
			}
		} else if ($operation == 'create') {
			$columns = [];
			$columns = $this->table_model->columns($auths['auths']->table_name);

			if ($auths['auths']->create_hide != null) {
				foreach ($columns as $key => $value) {

					if (array_search($key, explode(',', $auths['auths']->create_hide)) > -1 ? true : false) {
						unset($columns[$key]);
					}
				}
			}
		} else if ($operation == 'edit') {
			$columns = [];
			$columns = $this->table_model->columns($auths['auths']->table_name);
			if ($auths['auths']->edit_hide != null) {
				foreach ($columns as $key => $value) {
					if (array_search($key, explode(',', $auths['auths']->edit_hide)) > -1) {
						unset($columns[$key]);
					}
				}
			}
		}

		return $columns;
	}
	public function file_upload($filename)
	{
		if ($this->input->method()) {
			if ($_FILES) {
				$config['upload_path'] = './uploads/';
				$config['allowed_types'] = '*';
				$config['max_size'] = '0';
				$config['max_filename'] = '255';
				$config['encrypt_name'] = TRUE;

				$this->load->library('upload', $config);

				if (file_exists($config['upload_path'] . $_FILES[$filename]['name'])) {
					echo ('File already exists => ' . $config['upload_path'] . $_FILES[$filename]['name']);
					return;
				} else {
					if (!file_exists($config['upload_path'])) {
						mkdir($config['upload_path'], 0777, true);
					}
					$this->upload->do_upload($filename);
					$image_uploaded = $this->upload->data();

					$filename = pathinfo($image_uploaded['full_path']);

					return $filename['basename'];
				}
			} else {

				return false;
			}
		}
	}
	public function image_upload($filename)
	{
		if ($this->input->method()) {
			if ($_FILES) {
				$config['upload_path'] = './uploads/';
				$config['allowed_types'] = '*';
				$config['max_size'] = '0';
				$config['max_filename'] = '255';
				$config['encrypt_name'] = TRUE;

				$this->load->library('upload', $config);

				if (file_exists($config['upload_path'] . $_FILES[$filename]['name'])) {
					echo ('File already exists => ' . $config['upload_path'] . $_FILES[$filename]['name']);
					return;
				} else {
					if (!file_exists($config['upload_path'])) {
						mkdir($config['upload_path'], 0777, true);
					}
					$this->upload->do_upload($filename);
					$image_uploaded = $this->upload->data();

					echo json_encode($image_uploaded);
					//$filename = pathinfo($image_uploaded['full_path']);

					//return $filename['basename'];
				}
			} else {

				return false;
			}
		}
	}
	public function email_send($email, $title, $message)
	{
		$this->load->library('email');

		$config = array();
		$config['protocol'] = 'smtp';
		$config['smtp_host'] = '';
		$config['smtp_user'] = '';
		$config['smtp_pass'] = '';
		$config['smtp_port'] = 465;
		$config['smtp_crypto'] = "ssl";
		$config['charset'] = "UTF-8";
		$config['wordwrap'] = TRUE;
		$config['mailtype'] = 'html';


		$this->email->initialize($config);
		$this->email->set_newline("\r\n");

		$this->email->from('', '');
		$this->email->to($email);
		$this->email->cc('');
		$this->email->bcc('');
		//$this->email->priority(3);

		$this->email->subject($title);
		$this->email->message($message);


		if ($this->email->send()) {
			return true;
		} else {
			show_error($this->email->print_debugger());
		}
	}
	public function getDogrulama()
	{

		$image = @imagecreatetruecolor(120, 30) or die("hata oluştu");

		// arkaplan rengi oluşturuyoruz
		$background = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
		imagefill($image, 0, 0, $background);
		$linecolor = imagecolorallocate($image, 0xCC, 0xCC, 0xCC);
		$textcolor = imagecolorallocate($image, 0x33, 0x33, 0x33);

		// rast gele çizgiler oluşturuyoruz
		for ($i = 0; $i < 6; $i++) {
			imagesetthickness($image, rand(1, 3));
			imageline($image, 0, rand(0, 30), 120, rand(0, 30), $linecolor);
		}


		// rastgele sayılar oluşturuyoruz
		$sayilar = '';
		for ($x = 15; $x <= 95; $x += 20) {
			$sayilar .= ($sayi = rand(0, 9));
			imagechar($image, rand(3, 5), $x, rand(2, 14), $sayi, $textcolor);
		}

		// sayıları session aktarıyoruz

		session_start();
		/*session is started if you don't write this line can't use $_Session  global variable*/
		$_SESSION["newsession"] = $sayilar;


		// resim gösteriliyor ve sonrasında siliniyor
		//header('Content-type: image/png');
		ob_start();
		imagepng($image);
		$imagedata = ob_get_clean();


		print '<p><img src="data:image/png;base64,' . base64_encode($imagedata) . '" alt="' . md5($sayilar) . '" id="cpt" width="96" height="48"/></p>';

		imagedestroy($image);
	}
	public function setDogrulama($data)
	{
		session_start();
		/*session is started if you don't write this line can't use $_Session  global variable*/

		/*session created*/
		dd($_SESSION);

		//echo $_SESSION['captcha'] == $data;
	}
}

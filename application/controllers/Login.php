<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login extends CI_Controller
{
    public $simple = 'fungitu2_Simple';
    public function __construct()
    {
        parent::__construct();
    }
    public function md5()
    {
        $body = json_decode($this->input->raw_input_stream);
        $text = $body->text ?? '';
        echo md5($text);
    }
    public function login()
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
        $body = json_decode($this->input->raw_input_stream);
        $email = $body->email ?? '';
        $password = md5($body->password);

        $this->load->model('table_model');
        $this->table_model->tableName = "users";



        $user = $this->table_model->get(['email' => $email, 'password' => $password]);
        header('Content-Type: application/json');
        if ($user) {
            $token = md5(time());
            $this->table_model->update(['id' => $user->id], ['token' => $token]);
            echo json_encode(['status' => 'success', 'message' => 'Giriş başarılı.', 'token' => $token]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Eposta yada şifre hatalı.']);
            $this->output->set_status_header(400);
            die();
        }
    }
    public function register()
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
        $body = json_decode($this->input->raw_input_stream);

        $email = $body->email ?? '';
        $password = md5($body->password);
        $name = $body->name ?? '';
        $surname = $body->surname ?? '';
        $phone = $body->phone ?? '';

        $this->load->model('table_model');
        $this->table_model->tableName = "users";

        $user = $this->table_model->get(['email' => $email]);
        header('Content-Type: application/json');
        if ($user) {
            echo json_encode(['status' => 'error', 'message' => 'Bu eposta kullanımaktadır.']);
            $this->output->set_status_header(400);
            die();
        } else {
            $this->table_model->add([
                'email' => $email,
                'password' => $password,
                'name' => $name,
                'surname' => $surname,
                'phone' => $phone,
                'auths_group_id' => "3",
            ]);
            echo json_encode(['status' => 'success', 'message' => 'Kullanıcı başarıyla kaydedildi']);
        }
    }
    public function profile()
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
        $this->load->model('table_model');
        $this->table_model->tableName = "users";


        $_Header = $this->input->request_headers();
        $token = $_Header['token'] ?? $_Header['Token'];


        header('Content-Type: application/json');
        if ((empty($token) || $token == 'null') && $token != 0) {
            echo json_encode(['status' => 'error', 'message' => 'Kullanıcı bulunamadı.']);
            $this->output->set_status_header(400);
            die();
        } else {
            $datas = $this->table_model->get(['token' => $token]);
            $columns = (array) $this->table_model->columns('users');
            unset($columns['password']);
            unset($columns['token']);
            unset($columns['id']);
            unset($columns['added_date']);
            unset($columns['forget']);
            unset($columns['own_id']);
            unset($columns['settings']);
            unset($columns['status']);
            unset($columns['updated_date']);
            unset($columns['user_id']);
            $newData = (object) [];

            foreach ($columns as $key => $value) {
                $newData->$key =  $datas->$key;
            }
            if ($newData) {
                echo json_encode(['status' => 'success', 'message' => 'Kullanıcı bulundu.', 'data' => $newData, 'columns' => $columns]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Kullanıcı bulunamadı.']);
                $this->output->set_status_header(400);
                die();
            }
        }
    }
    public function auths()
    {

        $this->load->model('table_model');
        $this->table_model->tableName = "users";


        $_Header = $this->input->request_headers();
        $token = $_Header['token'] ?? $_Header['Token'];

        if (empty($token)) {

            //$this->output->set_status_header(400);
            echo json_encode(['status' => 'error', 'message' => 'Kullanıcı bulunamadı.']);

            exit();
        } else {
            $user = $this->table_model->get(['token' => $token]);

            if ($user) {
                $this->table_model->tableName = "auths";
                $auths = $this->table_model->get_all(['auths_group_id' => $user->auths_group_id], "");


                echo json_encode(['status' => 'success', 'message' => 'Kullanıcı bulundu.', 'data' => $auths]);
                //$this->output->set_status_header(200);
                die();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Kullanıcı bulunamadı.']);
                $this->output->set_status_header(400);
                die();
            }
        }
    }
    public function newPassword()
    {
        $this->load->model('table_model');
        $this->table_model->tableName = "users";

        header('Content-Type: application/json');

        $post = $this->input->post();
        $get = (array) json_decode($this->input->raw_input_stream);
        $input = array();
        if (empty($post) == 1) {
            $input = $get;
        } else {
            $input = $post;
        }

        if ($input['newPass'] == $input['checkPass']) {
            $_Header = $this->input->request_headers();
            $token = $_Header['token'] ?? $_Header['Token'];
            $user = $this->table_model->get(['token' => $token]);
            if ($user->password == md5($input['old_pass'])) {
                $where = ['token' => $token];
                $updated = ['password' => $input['new_pass']];
                $ret = $this->table_model->update($where, $updated);
                if ($ret) {
                    $data = [
                        "status" => $ret ? "success" : "error",
                        "message" => 'Şifre Başarıyla değiştirildi'
                    ];
                    $this->output
                        ->set_content_type('application/json', 'utf-8')
                        ->set_output(json_encode($data))
                        ->_display();
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Kayıt sırasında bir hata ile karşılaşıldı.']);
                    $this->output->set_status_header(400);
                }

                die();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Şifrenizi yanlış girdiniz.']);
                $this->output->set_status_header(400);
                die();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Şifreler uyumsuz.']);
            $this->output->set_status_header(400);
            die();
        }
    }
    public function forget()
    {
        $this->load->model('table_model');
        $this->table_model->tableName = "users";

        header('Content-Type: application/json');

        $post = $this->input->post();
        $get = (array) json_decode($this->input->raw_input_stream);
        $input = array();
        if (empty($post) == 1) {
            $input = $get;
        } else {
            $input = $post;
        }

        $user = $this->table_model->get(['email' => $input['email']]);
        $pin = rand(100000, 999999);
        $where = ['email' => $input['email']];
        $updated = ['forget' => $pin];
        $ret = $this->table_model->update($where, $updated);
        if ($user && $ret) {
            if ($this->email_send($user->email, 'Sifremi unuttum', '
            <span>Yeni şifrenizi belirlemek için gerekli kod.</span>
            <br>
            <h1>' . $pin . '</h1>
            ')) {
                $data = [
                    "status" => "success",
                    "message" => 'Eposta başarıyla gönderildi.'
                ];
                $this->output
                    ->set_content_type('application/json', 'utf-8')
                    ->set_output(json_encode($data))
                    ->_display();
                die();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Eposta gönderilemedi.']);
                $this->output->set_status_header(400);
                die();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Kullanıcı bulunamadı.']);
            $this->output->set_status_header(400);
            die();
        }
    }
    public function forgetPassword()
    {
        $this->load->model('table_model');
        $this->table_model->tableName = "users";

        header('Content-Type: application/json');

        $post = $this->input->post();
        $get = (array) json_decode($this->input->raw_input_stream);
        $input = array();
        if (empty($post) == 1) {
            $input = $get;
        } else {
            $input = $post;
        }


        if ($this->table_model->get(['email' => $input['email'], 'forget' => $input['pin']])) {
            $where = ['email' => $input['email'], 'forget' => $input['pin']];
            $updated = ['password' => md5($input['password']), 'forget' => ''];
            $ret =  $this->table_model->update($where, $updated);
            if ($ret) {
                $data = [
                    "status" =>  "success",
                    "message" => 'Şifre değiştirildi.'
                ];
                $this->output
                    ->set_content_type('application/json', 'utf-8')
                    ->set_output(json_encode($data))
                    ->_display();
                die();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Şifre değiştirilemedi.']);
                $this->output->set_status_header(400);
                die();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Pin yanlış.']);
            $this->output->set_status_header(400);
            die();
        }
    }
    public function email_send($email, $title, $message)
    {
        $this->load->library('email');

        $config = array();
        $config['protocol'] = 'smtp';
        $config['smtp_host'] = 'mail.fungiturkey.org';
        $config['smtp_user'] = 'info@fungiturkey.org';
        $config['smtp_pass'] = 'fungiturkey34';
        $config['smtp_port'] = 465;
        $config['smtp_crypto'] = "ssl";
        $config['charset'] = "UTF-8";
        $config['wordwrap'] = TRUE;
        $config['mailtype'] = 'html';


        $this->email->initialize($config);
        $this->email->set_newline("\r\n");

        $this->email->from('info@fungiturkey.org', 'Fungi Turkey');
        $this->email->to($email);
        $this->email->cc('mail@fungiturkey.org');
        $this->email->bcc('iletisim@fungiturkey.org');
        //$this->email->priority(3);

        $this->email->subject($title);
        $this->email->message($message);


        if ($this->email->send()) {
            return true;
        } else {
            show_error($this->email->print_debugger());
        }
    }
    public function token_control()
    {

        $this->load->model('table_model');
        $this->table_model->tableName = "users";


        $_Header = $this->input->request_headers();
        $token = $_Header['token'] ?? $_Header['Token'];

        header('Content-Type: application/json');
        if (empty($token) || $token == 'null') {
            $this->output->set_status_header(400);
            die();
        } else {
            $user = $this->table_model->get(['token' => $token]);
            $user->password = '';
            if ($user) {
                $this->output->set_status_header(200);
                die();
            } else {
                $this->output->set_status_header(400);
                die();
            }
        }
    }
}

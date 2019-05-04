<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Blog extends CI_Controller {

    private $loggedIn = false;
    private $user = null;
    private $tpl = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('blog_model');
        $this->load->helper('url');

        if ($this->loggedIn = isset($this->session->userdata['logged_in'])) {
            $this->user = $this->session->userdata['logged_in'];
            $this->tpl = [
                'logged_in' => $this->loggedIn,
                'user' => $this->user,
            ];
        };
    }

    private function show_access_denied() {
        show_error('Access Denied', '403', '403');
    }

    private function renderFullPage($content_view, $data = []) {
        $data = array_merge($this->tpl, $data);

        $this->load->view('header', $data);
        $this->load->view($content_view, $data);
        $this->load->view('footer', $data);
    }

    public function index()
    {
        $this->tpl['title'] = 'Latest Posts';
        $this->tpl['posts'] = $this->blog_model->loadPosts();

        $this->renderFullPage('blog/blog_index');
    }

    public function showPost($slug) {
        if (!$post = $this->blog_model->getPostBySlug($slug)) {
            show_404();
        }

        $this->renderFullPage('blog/show_post', [
            'title' => $post['title'],
            'edit_url' => base_url('blog/editPost/' . $post['id']),
            'delete_url' => base_url('blog/deletePost/' . $post['id']),
            'post' => $post,
        ]);
    }

    public function addPost()
    {
        if (!$this->loggedIn){
            $this->show_access_denied();
        }

        $this->tpl['post_route'] = 'blog/addPost';
        $this->tpl['title'] = 'Add New Post';

        $this->load->helper('form');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('post_title', 'Title', 'trim|required|xss_clean');
        $this->form_validation->set_rules('post_slug', 'Slug', 'trim|xss_clean');
        $this->form_validation->set_rules('post_content', 'Content', 'trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $this->renderFullPage('blog/edit_post');
        } else {
            $data = array(
                'title' => $this->input->post('post_title'),
                'slug' => $this->input->post('post_slug'),
                'content' => $this->input->post('post_content')
            );

            $slug = url_title($data['slug'] ? $data['slug'] : $data['title']);

            if ($this->blog_model->addPost(
                $data['title'],
                $slug,
                $data['content'],
                $this->user['id'])) {
                redirect('blog/showPost/'. $slug);
            } else {
                $this->renderFullPage('blog/edit_post', [
                    'post_title' => $data['title'],
                    'post_slug' => $slug,
                    'post_content' => $data['content']
                ]);
            }
        }
    }

    public function editPost($id = "")
    {
        if (!$this->loggedIn){
            $this->show_access_denied();
        }

        if (!$post = $this->blog_model->getPostById($id)) {
            show_404();
        }

        $this->tpl['post_route'] = 'blog/editPost/' . $id;
        $this->tpl['title'] = 'Edit: ' . $post['title'];

        $this->load->helper('form');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('post_title', 'Title', 'trim|required|xss_clean');
        $this->form_validation->set_rules('post_slug', 'Slug', 'trim|xss_clean');
        $this->form_validation->set_rules('post_content', 'Content', 'trim|xss_clean');

        if ($this->form_validation->run() == FALSE) {
            $this->renderFullPage('blog/edit_post', [
                'post_title' => $post['title'],
                'post_slug' => $post['slug'],
                'post_content' => $post['content'],
            ]);
        } else {
            $data = array(
                'title' => $this->input->post('post_title'),
                'slug' => $this->input->post('post_slug'),
                'content' => $this->input->post('post_content')
            );

            $slug = url_title($data['slug'] ? $data['slug'] : $data['title']);

            if ($result = $this->blog_model->updatePost($post['id'], $data['title'], $slug, $data['content'])) {
                redirect('blog/showPost/'. $slug);
            } else {
                $this->renderFullPage('blog/edit_post', [
                    'post_title' => $data['title'],
                    'post_slug' => $slug,
                    'post_content' => $data['content'],
                ]);
            }
        }
    }

    public function deletePost($id) {
        if (!$post = $this->blog_model->getPostById($id)) {
            show_404();
        }

        if ($this->blog_model->deletePost($id)) {
            redirect(base_url('blog'), 'refresh');
        } else {
            redirect(base_url('blog/showPost/' . $id), 'refresh');
        }
    }
}

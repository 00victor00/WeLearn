<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Created by JetBrains PhpStorm.
 * User: root
 * Date: 16/05/12
 * Time: 17:37
 * To change this template use File | Settings | File Templates.
 */
class Feed extends Home_Controller
{

    function __construct()
    {
        parent::__construct();
    }
    public function index()
    {

    }

    public function criar_feed()
    {

        set_json_header();
        $isValid=false;
        $tipo=$this->input->post('tipo-feed');
        $this->load->library('form_validation');

        $this->form_validation->set_rules('conteudo-feed', 'conteudo-feed', 'required|max_length[1000]');
        if($tipo != WeLearn_Compartilhamento_TipoFeed::STATUS)
        {
            $this->form_validation->set_rules('descricao-feed', 'descricao-feed', 'callback_validar_descricao');
        }

        if($this->form_validation->run()===false)
        {
            $json = create_json_feedback(false, validation_errors_json());
            exit($json);
        }else{
            $this->load->helper('notificacao_js');
            switch($tipo)
            {
                case WeLearn_Compartilhamento_TipoFeed::VIDEO:
                     $isValid = $this->validar_video();
                     if(!$isValid)
                     {
                         $notificacoesFlash = create_notificacao_json(
                             'erro',
                             'A url do video enviado não é valida, verifique se a url está correta.'
                         );
                         $this->session->set_flashdata('notificacoesFlash', $notificacoesFlash);

                         log_message(
                             'error',
                             'A url do video enviado nao é valida'
                         );

                         $json = create_json_feedback(false);
                     }
                     break;
                case WeLearn_Compartilhamento_TipoFeed::IMAGEM:
                    $isValid = $this->validar_imagem();
                    if(!$isValid)
                    {
                        $notificacoesFlash = create_notificacao_json(
                            'erro',
                            'A url da imagem enviada não é valida, verifique se a url está correta.'
                        );
                        $this->session->set_flashdata('notificacoesFlash', $notificacoesFlash);

                        log_message(
                            'error',
                            'A url da imagem enviada nao é valida'
                        );

                        $json = create_json_feedback(false);
                    }
                    break;
                case WeLearn_Compartilhamento_TipoFeed::LINK;
                    $isValid = $this->validar_url();
                    if(!$isValid)
                    {
                        $notificacoesFlash = create_notificacao_json(
                            'erro',
                            'A url da imagem enviada não é valida, verifique se a url está correta.'
                        );
                        $this->session->set_flashdata('notificacoesFlash', $notificacoesFlash);

                        log_message(
                            'error',
                            'A url enviada nao é valida'
                        );

                        $json = create_json_feedback(false);
                    }
                    break;
                case WeLearn_Compartilhamento_TipoFeed::STATUS:
                    $isValid = true;
                    if(!$isValid)
                    {
                        $notificacoesFlash = create_notificacao_json(
                            'erro',
                            'A url da imagem enviada não é valida, verifique se a url está correta.'
                        );
                        $this->session->set_flashdata('notificacoesFlash', $notificacoesFlash);

                        log_message(
                            'error',
                            'O status enviado nao é valida'
                        );

                        $json = create_json_feedback(false);
                    }
                    break;

            }

            if($isValid){
                $feedDao = WeLearn_DAO_DAOFactory::create('FeedDAO');
                $feedUsuario = $feedDao->criarNovo();
                $criador=$this->autenticacao->getUsuarioAutenticado();
                $conteudo=$this->input->post('conteudo-feed');


                if($tipo != WeLearn_Compartilhamento_TipoFeed::STATUS)
                {
                    $descricao=$this->input->post('descricao-feed');
                    $feedUsuario->setDescricao($descricao);
                }

                $feedUsuario->setConteudo($conteudo);
                $feedUsuario->setTipo($tipo);
                $feedUsuario->setCriador($criador);
                $feedUsuario->setDataEnvio(time());
                try{

                    $feedDao->salvar($feedUsuario);
                    $notificacoesFlash = create_notificacao_json(
                        'sucesso',
                        'Feed enviado com sucesso!'
                    );
                    $this->session->set_flashdata('notificacoesFlash', $notificacoesFlash);
                    $json = create_json_feedback(true);
                }catch(cassandra_NotFoundException $e){
                    $json=create_json_feedback(false);
                }
            }

            echo $json;

        }
    }

    public function remover_feed($idFeed)
    {
        $feedDAO = WeLearn_DAO_DAOFactory::create('FeedDAO');
        $this->load->helper('notificacao_js');
        try{
            $feedDAO->remover($idFeed);
            $json=Zend_Json::encode(array( 'success' => true , 'notificacao'=> create_notificacao_array(
                'sucesso',
                'Compartilhamento removido com sucesso!'
            )
            ));
        }catch(cassandra_NotFoundException $e){
            log_message(
                'error',
                'falha ao remover timeline id feed '.$idFeed
            );

            $json=Zend_Json::encode(array( 'success' => false , 'notificacao'=> create_notificacao_array(
                'erro',
                'Falha ao remover Compartilhamento'
            )));
        }
        echo $json;
    }


    public function criar_timeline($idPerfil)
    {
        $this->load->helper('notificacao_js');
        $usuarioPerfil = WeLearn_DAO_DAOFactory::create('UsuarioDAO')->recuperar($idPerfil);
        $usuarioAutenticado = $this->autenticacao->getUsuarioAutenticado();
        if(($usuarioAutenticado->getId() == $usuarioPerfil->getId()) || ($usuarioAutenticado->getId() != $usuarioPerfil->getId()
        && $usuarioPerfil->getConfiguracao()->getPrivacidadeCompartilhamento() == WeLearn_Usuarios_PrivacidadeCompartilhamento::HABILITADO))
        {
            set_json_header();
            $isValid=false;
            $tipo=$this->input->post('tipo-feed');
            $this->load->library('form_validation');

            $this->form_validation->set_rules('conteudo-feed', 'conteudo-feed', 'required');
            if($tipo != WeLearn_Compartilhamento_TipoFeed::STATUS)
            {
                $this->form_validation->set_rules('descricao-feed', 'descricao-feed', 'callback_validar_descricao');
            }

            if($this->form_validation->run()===false)
            {
                $json = create_json_feedback(false, validation_errors_json());
                exit($json);
            }else{

                switch($tipo)
                {
                    case WeLearn_Compartilhamento_TipoFeed::VIDEO:
                        $isValid = $this->validar_video();
                        if(!$isValid)
                        {
                            $notificacoesFlash = create_notificacao_json(
                                'erro',
                                'A url do video enviado não é valida, verifique se a url está correta.'
                            );
                            $this->session->set_flashdata('notificacoesFlash', $notificacoesFlash);

                            log_message(
                                'error',
                                'A url do video enviado nao é valida'
                            );

                            $json = create_json_feedback(false);
                        }
                        break;
                    case WeLearn_Compartilhamento_TipoFeed::IMAGEM:
                        $isValid = $this->validar_imagem();
                        if(!$isValid)
                        {
                            $notificacoesFlash = create_notificacao_json(
                                'erro',
                                'A url da imagem enviada não é valida, verifique se a url está correta.'
                            );
                            $this->session->set_flashdata('notificacoesFlash', $notificacoesFlash);

                            log_message(
                                'error',
                                'A url da imagem enviada nao é valida'
                            );

                            $json = create_json_feedback(false);
                        }
                        break;
                    case WeLearn_Compartilhamento_TipoFeed::LINK;
                        $isValid = $this->validar_url();
                        if(!$isValid)
                        {
                            $notificacoesFlash = create_notificacao_json(
                                'erro',
                                'A url da imagem enviada não é valida, verifique se a url está correta.'
                            );
                            $this->session->set_flashdata('notificacoesFlash', $notificacoesFlash);

                            log_message(
                                'error',
                                'A url enviada nao é valida'
                            );

                            $json = create_json_feedback(false);
                        }
                        break;
                    case WeLearn_Compartilhamento_TipoFeed::STATUS:
                        $isValid = true;
                        if(!$isValid)
                        {
                            $notificacoesFlash = create_notificacao_json(
                                'erro',
                                'A url da imagem enviada não é valida, verifique se a url está correta.'
                            );
                            $this->session->set_flashdata('notificacoesFlash', $notificacoesFlash);

                            log_message(
                                'error',
                                'O status enviado nao é valida'
                            );

                            $json = create_json_feedback(false);
                        }
                        break;

                }


                if($isValid){
                    $feedDao = WeLearn_DAO_DAOFactory::create('FeedDAO');
                    $feed = $feedDao->criarNovo();
                    $criador=$this->autenticacao->getUsuarioAutenticado();
                    $conteudo=$this->input->post('conteudo-feed');


                    if($tipo != WeLearn_Compartilhamento_TipoFeed::STATUS)
                    {
                        $descricao=$this->input->post('descricao-feed');
                        $feed->setDescricao($descricao);
                    }

                    $feed->setConteudo($conteudo);
                    $feed->setTipo($tipo);
                    $feed->setCriador($criador);
                    $feed->setDataEnvio(time());
                    try{

                        $feedDao->salvarTimeLine($feed,$usuarioPerfil);
                        $notificacoesFlash = create_notificacao_json(
                            'sucesso',
                            'Feed enviado com sucesso!'
                        );
                        $this->session->set_flashdata('notificacoesFlash', $notificacoesFlash);
                        $json = create_json_feedback(true);

                        //Notificar ao usuário
                        $notificacao = new WeLearn_Notificacoes_NotificacaoCompartilhamentoPerfil();
                        $notificacao->setCompartilhamento( $feed );
                        $notificacao->setDestinatario( $usuarioPerfil );
                        $notificacao->adicionarNotificador( new WeLearn_Notificacoes_NotificadorCassandra() );
                        $notificacao->adicionarNotificador( new WeLearn_Notificacoes_NotificadorTempoReal() );
                        $notificacao->notificar();
                        //fim da notificação.

                    }catch(cassandra_NotFoundException $e){
                        $json=create_json_feedback(false);
                    }
                }
            }
        }else{
            $notificacoesFlash = create_notificacao_json(
                'erro',
                'As configurações de privacidade não permitem que o compartilhamento seja enviado!'
            );
            $this->session->set_flashdata('notificacoesFlash', $notificacoesFlash);
            $json=create_json_feedback(false);
        }
        echo $json;
    }

    public function remover_timeline($idFeed,$idUsuario)
    {
        $feedDAO = WeLearn_DAO_DAOFactory::create('FeedDAO');
        $usuarioObj= WeLearn_DAO_DAOFactory::create('UsuarioDAO')->recuperar($idUsuario);
        $this->load->helper('notificacao_js');
        try{
            $feedObj = $feedDAO->recuperar($idFeed);
            $feedDAO->removerTimeline($feedObj,$usuarioObj);
            $json=Zend_Json::encode(array( 'success' => true , 'notificacao'=> create_notificacao_array(
                'sucesso',
                'Feed removido com sucesso!'
            )
            ));
        }catch(cassandra_NotFoundException $e){
            log_message(
                'error',
                'falha ao remover timeline id feed '.$idFeed.'id usuario '.$idUsuario
            );

            $json=Zend_Json::encode(
                array( 'success' => false ,
                    'notificacao'=> create_notificacao_array( 'erro','O feed selecionado já foi removido!'))
            );


        }
        echo $json;
    }

    public function validar_descricao($descricao)
    {
        $descricao = trim($descricao);
        if (empty($descricao))
        {
            $this->form_validation->set_message('validar_descricao', 'O campo %s é necessario');
            return FALSE;
        }else if(strlen($descricao)>1000){
            $this->form_validation->set_message('validar_descricao', 'O campo %s não pode exceder 1000 caracteres');
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }


    private function validar_video()
    {
        $video = $this->input->post('conteudo-feed');
        $this->load->library('autoembed');
        $isValid = $this->autoembed->parseUrl($video);
        return $isValid;
    }

    private function validar_url()
    {
        $url = $this->input->post('conteudo-feed');
        $http = strpos($url,"http://");
        if($http == false){
            $url = 'http://'.$url;
        }
        if(filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            return false;
        } else {
            return true;
        }
    }

    private function validar_imagem()
    {
        $imagem = $this->input->post('conteudo-feed');
        $isValid = @getimagesize($imagem);
        return $isValid;
    }
}



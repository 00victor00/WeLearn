<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Area extends Home_Controller {

    /**
     * Construtor carrega configurações da classes base CI_Controller
     * (Resolve bug ao utilizar this->load)
     */
    function __construct()
    {
        parent::__construct();
    }

	public function index()
	{
        $partialCriar = $this->template->loadPartial('form', array(),'administracao/area');
        $this->_renderTemplateHome();

    }

    public function adicionar()
    {
        if ( ! $this->input->is_ajax_request() ) {
            show_404();
        }

        set_json_header();

        $this->load->library('form_validation');
        if ($this->form_validation->run() === FALSE) {
            $json = create_json_feedback(false, validation_errors_json());
            exit($json);
        }

        try {
            $areaDao = WeLearn_DAO_DAOFactory::create('AreaDAO');
            $novaArea = $areaDao->criarNovo($this->input->post());

            $areaDao->salvar($novaArea);

            $json = create_json_feedback(true);
            exit($json);

        } catch (Exception $e) {
            log_message('error', 'Erro ao adicionar Área. ' . create_exception_description($e));

            $errors = create_json_feedback_error_json(
                'Ocorreu um erro ao adicionar uma nova Área.<br/>'
               .'Ja estamos verificando. Tente novamente em breve'
            );

            $json = create_json_feedback(false, $errors);
            exit($json);
        }
    }



    public function criar($idArea){

        try{



        }catch (Exception $e) {
                log_message('error', 'Erro ao exibir formulário de criação de categoria de fórum: ' . create_exception_description($e));

                show_404();
            }
    }
    protected function _renderTemplateHome($view = '', $dados = null)
    {
        $this->_barraDireitaSetVar(
            'menuContexto',
            $this->template->loadPartial( 'menu', Array(), 'administracao' )
        );

        parent::_renderTemplateHome($view, $dados);
    }

}

/* End of file area.php */
/* Location: ./application/controllers/administracao/area.php */
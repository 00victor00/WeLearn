<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sugestao extends WL_Controller {

    /**
     * Construtor carrega configurações da classes base CI_Controller
     * (Resolve bug ao utilizar this->load)
     */
    function __construct()
    {
        parent::__construct();

        $this->template->appendJSImport('curso.js')
                ->appendJSImport('sugestao_curso.js');
    }

	public function index()
	{
        
    }

    public function listar()
    {
        try {
            $sugestaoDao = WeLearn_DAO_DAOFactory::create('SugestaoCursoDAO');
            $sugestoesRecentes = $sugestaoDao->recuperarTodos();

            $dadosView = array(
                'sugestoes' => $sugestoesRecentes
            );

            $this->template->render('curso/sugestao/lista', $dadosView);
        } catch (Exception $e) {
            echo create_exception_description($e);
        }
    }

    public function criar()
    {
        $areaDao = WeLearn_DAO_DAOFactory::create('AreaDAO');
        $listaAreasObjs = $areaDao->recuperarTodos();

        $this->load->helper('area');
        $listaAreas = lista_areas_para_dados_dropdown($listaAreasObjs);

        $listaSegmentos = array(
            '0' => 'Selecione uma área de segmento'
        );

        $dadosFormPartial = array(
            'formAction' => '/curso/sugestao/salvar',
            'extraOpenForm' => 'id="form-sugestao"',
            'tituloForm' => 'Descreva a sua Sugestão',
            'nomeAtual' => '',
            'temaAtual' => '',
            'descricaoAtual' => '',
            'listaAreas' => $listaAreas,
            'areaAtual' => '0',
            'listaSegmentos' => $listaSegmentos,
            'segmentoAtual' => '0'
        );

        $formCriar = $this->template->loadPartial('form', $dadosFormPartial, 'curso/sugestao');

        $this->template->render('curso/sugestao/criar', array('formCriar' => $formCriar));
    }

    public function salvar()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        set_json_header();

        $this->load->library('form_validation');
        if ($this->form_validation->run() === FALSE) {
            $errors = validation_errors_json();

            $json = create_json_feedback(false, $errors);
        } else {
            try {
                $dadosForm = $this->input->post();

                $area = new WeLearn_Cursos_Area($dadosForm['area']);
                $segmento = new WeLearn_Cursos_Segmento($dadosForm['segmento'], '', $area);
                $dadosForm['segmento'] = $segmento;

                $sugestaoCursoDao = WeLearn_DAO_DAOFactory::create('SugestaoCursoDAO');
                $novaSugestao = $sugestaoCursoDao->criarNovo($dadosForm);

                $novaSugestao->setCriador($this->autenticacao->getUsuarioAutenticado());

                $sugestaoCursoDao->salvar($novaSugestao);

                $json = create_json_feedback(true);
            } catch(Exception $e) {
                log_message('error', 'Erro ao salvar sugestão de curso: ' . create_exception_description($e));

                $error = create_json_feedback_error_json('Não foi possível criar a sugestão de curso, tente novamente mais tarde.');

                $json = create_json_feedback(false, $error);
            }
        }

        echo $json;
    }
}

/* End of file sugestao.php */
/* Location: ./application/controllers/curso/sugestao.php */
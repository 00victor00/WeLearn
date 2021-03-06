<?php
/**
 * Created by Allan Marques
 * Date: 22/07/11
 * Time: 00:20
 *
 * Description:
 *
 */

class WeLearn_Cursos_Conteudo_Aula extends WeLearn_DTO_AbstractDTO
{
    /**
     * @var string
     */
    private $_id;

    /**
     * @var string
     */
    private $_nome;

    /**
     * @var string
     */
    private $_descricao;

    /**
     * @var int
     */
    private $_nroOrdem;

    /**
     * @var WeLearn_Cursos_Conteudo_Modulo
     */
    private $_modulo;

    /**
     * @var int
     */
    private $_qtdTotalPaginas;

    /**
     * @var int
     */
    private $_qtdTotalRecursos;

    /**
     * @param string $descricao
     */
    public function setDescricao($descricao)
    {
        $this->_descricao = (string)$descricao;
    }

    /**
     * @return string
     */
    public function getDescricao()
    {
        return $this->_descricao;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->_id = (string)$id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param \WeLearn_Cursos_Conteudo_Modulo $modulo
     */
    public function setModulo(WeLearn_Cursos_Conteudo_Modulo $modulo)
    {
        $this->_modulo = $modulo;
    }

    /**
     * @return \WeLearn_Cursos_Conteudo_Modulo
     */
    public function getModulo()
    {
        return $this->_modulo;
    }

    /**
     * @param string $nome
     */
    public function setNome($nome)
    {
        $this->_nome = (string)$nome;
    }

    /**
     * @return string
     */
    public function getNome()
    {
        return $this->_nome;
    }

    /**
     * @param int $nroOrdem
     */
    public function setNroOrdem($nroOrdem)
    {
        $this->_nroOrdem = (int)$nroOrdem;
    }

    /**
     * @return int
     */
    public function getNroOrdem()
    {
        return $this->_nroOrdem;
    }

    /**
     * @param int $qtdTotalPaginas
     */
    public function setQtdTotalPaginas($qtdTotalPaginas)
    {
        $this->_qtdTotalPaginas = (int)$qtdTotalPaginas;
    }

    /**
     * @return int
     */
    public function getQtdTotalPaginas()
    {
        return $this->_qtdTotalPaginas;
    }

    /**
     * @param int $qtdTotalRecursos
     */
    public function setQtdTotalRecursos($qtdTotalRecursos)
    {
        $this->_qtdTotalRecursos = (int)$qtdTotalRecursos;
    }

    /**
     * @return int
     */
    public function getQtdTotalRecursos()
    {
        return $this->_qtdTotalRecursos;
    }

    /**
     * Converte os dados das propriedades do objeto para uma relação 'propriedade => valor'
     * em um array.
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'nome' => $this->getNome(),
            'descricao' => $this->getDescricao(),
            'nroOrdem' => $this->getNroOrdem(),
            'modulo' => $this->getModulo()->toArray(),
            'qtdTotalPaginas' => $this->getQtdTotalPaginas(),
            'qtdTotalRecursos' => $this->getQtdTotalRecursos(),
            'persistido' => $this->isPersistido()
        );
    }

    /**
     * Converte os dados das propriedades do objeto em um array para ser persistido no BD Cassandra
     *
     * @return array
     */
    public function toCassandra()
    {
        return array(
            'id' => $this->getId(),
            'nome' => $this->getNome(),
            'descricao' => $this->getDescricao(),
            'nroOrdem' => $this->getNroOrdem(),
            'modulo' => ($this->_modulo instanceof WeLearn_Cursos_Conteudo_Modulo) ?
                $this->getModulo()->getId() : ''
        );
    }
}

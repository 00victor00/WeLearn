<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Thiago Monteiro
 * Date: 09/08/11
 * Time: 09:40
 * To change this template use File | Settings | File Templates.
 */
 
class WeLearn_DAO_AlternativaAvaliacaoDAO extends WeLearn_DAO_AbstractDAO {

    /**
     * @param mixed $de
     * @param mixed $ate
     * @param array|null $filtros
     * @return array
     */
    public function recuperarTodos($de = null, $ate = null, array $filtros = null)
    {
        /*
         * implementar metodo
         * */
    }

    /**
     * @param mixed $id
     * @return WeLearn_DTO_IDTO
     */
    public function recuperar($id)
    {
        /*
         * implementar metodo
         */
    }

    /**
     * @param mixed $de
     * @param mixed $ate
     * @return int
     */
    public function recuperarQtdTotal($de = null, $ate = null)
    {
        /*
         * implementar metodo
         */
    }

    /**
     * @param WeLearn_DTO_IDTO $dto
     * @return boolean
     */
    public function salvar()
    {
        /*
         * implementar metodo
         */
    }

     /**
     * @param mixed $id
     * @return WeLearn_DTO_IDTO
     */
    public function remover($id)
    {
        /*
         * implementar metodo
         */
    }

     /**
     * @param array|null $dados
     * @return WeLearn_DTO_IDTO
     */
    public function criarNovo(array $dados = null)
    {
       /*
         * implementar metodo
         */
    }

    /**
     * @param WeLearn_DTO_IDTO $dto
     * @return boolean
     */
    public function _adicionar(WeLearn_DTO_IDTO &$dto)
    {
       /*
         * implementar metodo
         */
    }

     /**
     * @param WeLearn_DTO_IDTO $dto
     * @return boolean
     */
    public function _atualizar(WeLearn_DTO_IDTO $dto)
    {
        /*
         * implementar metodo
         */
    }

}
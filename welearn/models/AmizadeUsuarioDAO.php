<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Thiago Monteiro
 * Date: 11/08/11
 * Time: 09:22
 * To change this template use File | Settings | File Templates.
 */

class AmizadeUsuarioDAO extends WeLearn_DAO_AbstractDAO
{
    protected $_nomeCF = 'usuarios_amizade';

    //indexes
    private $_nomeAmizadeAmigosCF = 'usuarios_amizade_amigos';
    private $_nomeAmizadeAmigosInativosCF = 'usuarios_amizade_amigos_inativos';
    private $_nomeAmizadeRequisicoesCF = 'usuarios_amizade_requisicoes';
    private $_nomeAmizadeRequisicoesPorDataCF = 'usuarios_amizade_requisicoes_por_data';

    private $_amizadeAmigosCF;
    private $_amizadeAmigosInativosCF;
    private $_amizadeRequisicoesCF;
    private $_amizadeRequisicoesPorDataCF;

    /**
     * @var UsuarioDAO
     */
    private $_usuarioDao;

    function __construct()
    {
        $phpCassa = WL_Phpcassa::getInstance();

        $this->_amizadeAmigosCF = $phpCassa->getColumnFamily($this->_nomeAmizadeAmigosCF);
        $this->_amizadeAmigosInativosCF = $phpCassa->getColumnFamily($this->_nomeAmizadeAmigosInativosCF);
        $this->_amizadeRequisicoesCF = $phpCassa->getColumnFamily($this->_nomeAmizadeRequisicoesCF);
        $this->_amizadeRequisicoesPorDataCF = $phpCassa->getColumnFamily($this->_nomeAmizadeRequisicoesPorDataCF);

        $this->_usuarioDao = WeLearn_DAO_DAOFactory::create('UsuarioDAO');
    }

    /**
     * @param mixed $id
     * @return WeLearn_DTO_IDTO
     */
    public function recuperar($id)
    {
        $column = $this->_cf->get($id);
        return $this->_criarFromCassandra($column);
    }

    /**
     * @param WeLearn_Usuarios_Usuario $usuarioAtual
     * @param WeLearn_Usuarios_Usuario $amigo
     * @return WeLearn_Usuarios_AmizadeUsuario
     */
    public function recuperarDeUsuarioAtual(WeLearn_Usuarios_Usuario $usuarioAtual,
                                            WeLearn_Usuarios_Usuario $amigo)
    {
        $idAmizade = $this->gerarIdAmizade( $usuarioAtual, $amigo );

        $column = $this->_cf->get( $idAmizade );

        return $this->_criarFromCassandra($column, $usuarioAtual, $amigo);
    }

    /**
     * @param mixed $de
     * @param mixed $ate
     * @param array|null $filtros
     * @return array
     */
    public function recuperarTodos($de = '', $ate = '', array $filtros = null)
    {
        if(isset($filtros['count'])) {
            $count = $filtros['count'];
        } else {
            $count = 10;
        }

        if (isset($filtros['opcao'])) {
            switch ($filtros['opcao']) {
                case 'amigos':
                    return $this->recuperarTodosAmigos($filtros['usuario'], $de, $ate, $count);
                case 'amigosPorData':
                    return $this->recuperarTodosAmigosPorData($filtros['usuario'], $de, $ate, $count);
                case 'requisicoes':
                    return $this->recuperarTodasRequisicoes($filtros['usuario'], $de, $ate, $count);
                case 'requisicoesPorData':
                    return $this->recuperarTodasRequisicoesPorData($filtros['usuario'], $de, $ate, $count);
                default:
            }
        }

        return $this->recuperarTodosAmigos($filtros['usuario'], $de, $ate, $count);
    }

    public function recuperarTodosAmigos(WeLearn_Usuarios_Usuario $usuario, $de = '', $ate = '', $count = 10)
    {
        $idsAmigos = array_keys(
            $this->_amizadeAmigosCF->get($usuario->getId(),
                null,
                $de,
                $ate,
                false,
                $count)
        );

        return $this->_recuperarUsuariosPorIds($idsAmigos);
    }



    public function recuperarTodasRequisicoes(WeLearn_Usuarios_Usuario $usuario, $de = '', $ate = '', $count = 10)
    {
        if ($de != '') {
            $de = CassandraUtil::import($de)->bytes;
        }
        if ($ate != '') {
            $ate = CassandraUtil::import($ate)->bytes;
        }

        $idsAmigos = array_keys(
            $this->_amizadeRequisicoesCF->get($usuario->getId(),
                null,
                $de,
                $ate,
                false,
                $count)
        );

        return $this->_recuperarUsuariosPorIds($idsAmigos);
    }

    public function recuperarTodasRequisicoesPorData(WeLearn_Usuarios_Usuario $usuario, $de = '', $ate = '', $count = 10)
    {
        if ($de != '') {
            $de = CassandraUtil::import($de)->bytes;
        }
        if ($ate != '') {
            $ate = CassandraUtil::import($ate)->bytes;
        }

        $idsAmigos = array_values(
            $this->_amizadeRequisicoesPorDataCF->get($usuario->getId(),
                null,
                $de,
                $ate,
                true,
                $count)
        );

        return $this->_recuperarUsuariosPorIds($idsAmigos);
    }

    public function recuperarAmigosAleatorios(WeLearn_Usuarios_Usuario $usuario, $qtd)
    {
        $idsAmigos = array_keys(
            $this->_amizadeAmigosCF->get($usuario->getId(),
                null,
                '',
                '',
                false,
                1000000)
        );

        $totalAmigos = count( $idsAmigos );

        if ( $qtd > $totalAmigos ) {

            $qtd = $totalAmigos;

        }

        $arrayAmigos = array();

        for ( $i = 0; $i < $qtd; $i++ ) {

            $key = array_rand( $idsAmigos );

            if ( isset( $idsAmigos[ $key ] ) ) {

                $arrayAmigos[] = $idsAmigos[ $key ];

                unset( $idsAmigos[ $key ] );

            } else {

                $i--;

            }

        }

        return $this->_recuperarUsuariosPorIds( $arrayAmigos );
    }

    public function recuperarTodosAmigosInativos(WeLearn_Usuarios_Usuario $usuario, $de = '', $ate = '', $count = 1000000)
    {
        $idsAmigos = array_keys(
            $this->_amizadeAmigosInativosCF->get($usuario->getId(),
                null,
                $de,
                $ate,
                false,
                $count)
        );

        return $idsAmigos;
    }

    public function recuperarTodosAmigosAtivos(WeLearn_Usuarios_Usuario $usuario, $de = '', $ate = '', $count = 1000000)
    {
        $idsAmigos = array_keys(
            $this->_amizadeAmigosCF->get($usuario->getId(),
                null,
                $de,
                $ate,
                false,
                $count)
        );

        return $idsAmigos;
    }

    /**
     * @param mixed $de
     * @param mixed $ate
     * @return int
     */
    public function recuperarQtdTotal($de = null, $ate = null)
    {
        return $this->recuperarQtdTotalAmigos($de);
    }

    public function recuperarQtdTotalAmigos(WeLearn_Usuarios_Usuario $usuario)
    {
        return $this->_amizadeAmigosCF->get_count($usuario->getId());
    }

    public function recuperarQtdTotalRequisicoes(WeLearn_Usuarios_Usuario $usuario)
    {
        return $this->_amizadeRequisicoesCF->get_count($usuario->getId());
    }

    /**
     * @param mixed $id
     * @return WeLearn_DTO_IDTO
     */
    public function remover($id)
    {
        $column = $this->_cf->get($id);
        $amizadeInativa = $this->_criarFromCassandra($column);
        $amizadeInativa->setStatus(WeLearn_Usuarios_StatusAmizade::NAO_AMIGOS);
        $this->_cf->insert($id,$amizadeInativa->toCassandra());

        $this->_amizadeAmigosCF->remove(
            $amizadeInativa->getUsuario()->getId(),
            array($amizadeInativa->getAmigo()->getId())
        );

        $this->_amizadeAmigosCF->remove(
            $amizadeInativa->getAmigo()->getId(),
            array($amizadeInativa->getUsuario()->getId())
        );

        $this->_amizadeAmigosInativosCF->insert(
            $amizadeInativa->getUsuario()->getId(),
            array($amizadeInativa->getAmigo()->getId() => '')
        );

        $this->_amizadeAmigosInativosCF->insert(
            $amizadeInativa->getAmigo()->getId(),
            array($amizadeInativa->getUsuario()->getId() => '')
        );

        $amizadeInativa->setPersistido(false);

        return $amizadeInativa;
    }

    /**
     * @param array|null $dados
     * @return WeLearn_DTO_IDTO
     */
    public function criarNovo(array $dados = null)
    {
        $novaAmizade = new WeLearn_Usuarios_AmizadeUsuario();
        $novaAmizade->preencherPropriedades($dados);

        return $novaAmizade;
    }

    /**
     * @param WeLearn_DTO_IDTO $dto
     * @return boolean
     */
    protected function _atualizar(WeLearn_DTO_IDTO $dto)
    {
        $idAmizade = $this->gerarIdAmizade($dto->getUsuario(), $dto->getAmigo());

        $statusArray = $this->_cf->get($idAmizade, array('status','timeUUID'));
        $statusAntigo = $statusArray['status'];
        $timeUUID = CassandraUtil::import($statusArray['timeUUID'])->bytes;

        $this->_cf->insert($idAmizade, $dto->toCassandra());

        if ($statusAntigo != $dto->getStatus()) {
            if ( (int)$statusAntigo === WeLearn_Usuarios_StatusAmizade::REQUISICAO_EM_ESPERA ) {
                $this->_amizadeRequisicoesCF->remove(
                    $dto->getUsuario()->getId(),
                    array($dto->getAmigo()->getId())
                );

                $this->_amizadeRequisicoesPorDataCF->remove(
                    $dto->getUsuario()->getId(),
                    array($timeUUID)
                );

                $this->_amizadeAmigosCF->insert(
                    $dto->getUsuario()->getId(),
                    array($dto->getAmigo()->getId() => '')
                );

                $this->_amizadeAmigosCF->insert(
                    $dto->getAmigo()->getId(),
                    array($dto->getUsuario()->getId() => '')
                );

                $this->_amizadeAmigosInativosCF->remove($dto->getAmigo()->getId(),array($dto->getUsuario()->getId()));
                $this->_amizadeAmigosInativosCF->remove($dto->getUsuario()->getId(),array($dto->getAmigo()->getId()));


            } else {
                $this->_amizadeAmigosCF->remove(
                    $dto->getUsuario()->getId(),
                    array($dto->getAmigo()->getId())
                );



                $this->_amizadeRequisicoesCF->insert(
                    $dto->getUsuario()->getId(),
                    array($dto->getAmigo()->getId() => '')
                );


            }
        }

    }

    /**
     * @param WeLearn_DTO_IDTO $dto
     * @return boolean
     */
    protected function _adicionar(WeLearn_DTO_IDTO &$dto)
    {
        $amizadeColumns = $dto->toCassandra();

        $this->_cf->insert($amizadeColumns['id'], $amizadeColumns);

        $timeUUID = CassandraUtil::import($amizadeColumns['timeUUID'])->bytes;

        $this->_amizadeRequisicoesCF->insert(
            $dto->getUsuario()->getId(),
            array($dto->getAmigo()->getId() => '')
        );

        $this->_amizadeRequisicoesPorDataCF->insert(
            $dto->getUsuario()->getId(),
            array($timeUUID => $dto->getAmigo()->getId() )
        );

        $dto->setPersistido(true);
    }

    public function saoAmigos(WeLearn_Usuarios_Usuario $usuarioAutenticado,
                              WeLearn_Usuarios_Usuario $usuarioPerfil)
    {
        try {
            $amizade = $this->recuperarDeUsuarioAtual(
                $usuarioAutenticado,
                $usuarioPerfil
            );

            return $amizade->getStatus();

        } catch (cassandra_NotFoundException $e) {
            return WeLearn_Usuarios_StatusAmizade::NAO_AMIGOS;
        }
    }

    public function gerarIdAmizade(WeLearn_Usuarios_Usuario $usuario, WeLearn_Usuarios_Usuario $amigo)
    {
        $arraySort = array($usuario->getId(), $amigo->getId());
        sort($arraySort);

        return implode('::', $arraySort);
    }

    /**
     * @param $idAmizade
     * @param null|WeLearn_Usuarios_Usuario $usuarioAtual
     * @param WeLearn_Usuarios_Usuario $amigoAtual
     * @return array
     */
    private function _recuperarUsuariosDeIdAmizade($idAmizade,
                                                   WeLearn_Usuarios_Usuario $usuarioAtual = null,
                                                   WeLearn_Usuarios_Usuario $amigoAtual = null) {
        $arrayIdUsuarios = explode('::', $idAmizade);
        $arrayRetorno = array();

        if ($usuarioAtual instanceof WeLearn_Usuarios_Usuario) {
            $arrayRetorno['usuario'] = $usuarioAtual;
            if ($usuarioAtual instanceof WeLearn_Usuarios_Usuario) {
                $arrayRetorno['amigo'] = $amigoAtual;

                return $arrayRetorno;
            } elseif($arrayIdUsuarios[0] == $usuarioAtual->getId()) {
                $arrayRetorno['amigo'] = $this->_usuarioDao->recuperar($arrayIdUsuarios[1]);

                return $arrayRetorno;
            } elseif($arrayRetorno[1] == $usuarioAtual->getId()) {
                $arrayRetorno['amigo'] = $this->_usuarioDao->recuperar($arrayIdUsuarios[0]);

                return $arrayRetorno;
            }
        }

        $arrayRetorno['usuario'] = $this->_usuarioDao->recuperar($arrayIdUsuarios[0]);
        $arrayRetorno['amigo'] = $this->_usuarioDao->recuperar($arrayIdUsuarios[1]);

        return $arrayRetorno;
    }

    private function _criarFromCassandra(array $column,
                                         WeLearn_Usuarios_Usuario $usuarioPadrao = null,
                                         WeLearn_Usuarios_Usuario $amigoPadrao = null)
    {
        $column = array_merge(
            $column,
            $this->_recuperarUsuariosDeIdAmizade(
                $column['id'],
                $usuarioPadrao,
                $amigoPadrao
            )
        );

        $amizade = new WeLearn_Usuarios_AmizadeUsuario();
        $amizade->fromCassandra($column);

        return $amizade;
    }

    private function _recuperarUsuariosPorIds(array $arrayIds)
    {
        $arrayUsuarios = array();

        foreach ($arrayIds as $id) {
            $arrayUsuarios[] = $this->_usuarioDao->recuperar($id);
        }

        return $arrayUsuarios;
    }
}
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: allan
 * Date: 08/05/12
 * Time: 20:29
 * To change this template use File | Settings | File Templates.
 */
class WeLearn_Usuarios_Autorizacao_PermissaoGerenciadorPrincipal extends WeLearn_Usuarios_Autorizacao_PermissaoGerenciadorAuxiliar
{
    public function __construct()
    {
        parent::__construct();

        $this->_aplicarPermissoesGerenciadorPrincipal();
    }

    protected function _aplicarPermissoesGerenciadorPrincipal()
    {
        $this->_permissoes = array_merge(
            $this->_permissoes,
            array(
                'gerenciador/convites' => true,
                'gerenciador/mais_convites' => true,
                'gerenciador/cancelar_convite' => true,
                'gerenciador/desvincular' => true,
                'gerenciador/convidar' => true,
                'gerenciador/buscar_usuarios' => true,
                'gerenciador/enviar_convites' => true,
                'curso/configurar' => true,
                'curso/salvar_imagem_temporaria' => true
            )
        );
    }
}

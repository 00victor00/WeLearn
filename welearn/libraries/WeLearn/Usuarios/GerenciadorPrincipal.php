<?php
/**
 * Created by Allan Marques
 * Date: 21/07/11
 * Time: 16:23
 *
 * Description:
 *
 */

class WeLearn_Usuarios_GerenciadorPrincipal extends WeLearn_Usuarios_GerenciadorAuxiliar
{
    protected $_nivelAcesso = WeLearn_Usuarios_Autorizacao_NivelAcesso::GERENCIADOR_PRINCIPAL;
}
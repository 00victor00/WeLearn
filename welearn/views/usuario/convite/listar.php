<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Thiago
 * Date: 01/05/12
 * Time: 23:54
 * To change this template use File | Settings | File Templates.
 */
?>
<?php if ($success==true): ?>
<header>
    <hgroup>
        <h1>convites <?=$tipo?></h1>
    </hgroup>
</header>

<?php if($haConvites): ?>
    <div>
        <a href="convite/proxima_pagina/" data-proximo="<?php echo $inicioProxPagina ?>" id="paginacao-convite">convites mais antigos</a>
        <input type='hidden' value='<?=$inicioProxPagina?>' id='id-prox-pagina'/>
    </div>
    <?php endif;?>

<ul id="lista-convites">
    <?=$partialListaConvites?>
</ul>
<?php else: ?>
<h3>Voce não possui nenhum convite pendente</h3>
<?php endif;?>
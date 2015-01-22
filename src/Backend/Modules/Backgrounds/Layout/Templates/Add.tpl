{include:{$BACKEND_CORE_PATH}/Layout/Templates/Head.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureStartModule.tpl}

<div class="pageTitle">
    <h2>{$lblBackgrounds|ucfirst}: {$lblAdd}</h2>
</div>

{form:add}
    <label for="title">{$lblTitle|ucfirst}</label>
    {$txtTitle} {$txtTitleError}

    <div id="pageUrl">
        <div class="oneLiner">
            {option:detailURL}<p><span><a href="{$detailURL}/{$item.url}">{$detailURL}/<span id="generatedUrl"></span></a></span></p>{/option:detailURL}
            {*{option:!detailURL}<p class="infoMessage">{$errNoModuleLinked}</p>{/option:!detailURL}*}
        </div>
    </div>


    <div class="tabs">
        <ul>
            <li><a href="#tabContent">{$lblContent|ucfirst}</a></li>
        </ul>

        <div id="tabContent">
            <table border="0" cellspacing="0" cellpadding="0" width="100%">
                <tr>
                    <td id="leftColumn">

                        <div class="box">
                            <div class="heading">
                                <h3>
                                    <label for="image">{$lblImage|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
                                </h3>
                            </div>
                            <div class="options">
                                {option:item}
                                    <p><img src="{$FRONTEND_FILES_URL}/Backgrounds/images/300x/{$item.image}"/></p>
                                {/option:item}
                                <p>
                                    {$fileImage} {$fileImageError}
                                </p>
                            </div>
                        </div>
                    </td>

                    <td id="sidebar">

                        <div class="box">
                            <div class="heading">
                                <h3>
                                    {$lblStatus|ucfirst}
                                </h3>
                            </div>
                            <div class="options">
                                <ul class="inputList">
                                    {iteration:hidden}
                                        <li>
                                            {$hidden.rbtHidden}
                                            <label for="{$hidden.id}">{$hidden.label}</label>
                                        </li>
                                    {/iteration:hidden}
                                </ul>
                            </div>
                        </div>

                        <div class="box">
                            <div class="heading">
                                <h3>{$lblOptions|ucfirst}</h3>
                            </div>
                            <div class="options">
                                <label for="backgroundSize">{$lblBackgroundSize|ucfirst}</label>
                                {$ddmBackgroundSize} {$ddmBackgroundSizeError}
                            </div>
                            <div class="options">
                                <label for="backgroundPosition">{$lblBackgroundPosition|ucfirst}</label>
                                {$ddmBackgroundPositionHorizontal} {$ddmBackgroundPositionVertical}
                                {$ddmBackgroundRepeatError} {$ddmBackgroundRepeatError}
                            </div>
                            <div class="options">
                                <label for="backgroundRepeat">{$lblBackgroundRepeat|ucfirst}</label>
                                {$ddmBackgroundRepeat} {$ddmBackgroundRepeatError}
                            </div>
                        </div>


                    </td>
                </tr>
            </table>
        </div>

    </div>

    <div class="fullwidthOptions">
        <div class="buttonHolderRight">
            <input id="addButton" class="inputButton button mainButton" type="submit" name="add" value="{$lblPublish|ucfirst}" />
        </div>
    </div>
{/form:add}

{include:{$BACKEND_CORE_PATH}/Layout/Templates/StructureEndModule.tpl}
{include:{$BACKEND_CORE_PATH}/Layout/Templates/Footer.tpl}

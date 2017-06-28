<?php
Yii::app()->clientScript->registerScript('editorfiletype',"editorfiletype ='".$sEditorFileType."';",CClientScript::POS_HEAD);
?>


<?php if (is_template_editable($templatename)==true): ?>
    <div class="row template-sum">
        <div class="col-lg-12">

            <?php App()->getClientScript()->registerPackage('jquery-ace'); ?>
            <h4><?php echo sprintf(gT("Viewing file '%s'"),$editfile); ?></h4>

            <?php if (!is_writable($templates[$templatename])):?>
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <?php eT("You can't save changes because the template directory is not writable."); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row template-sum">
        <div class="col-sm-2" id='templateleft'>
            <div >
                <?php eT("Screen part files:"); ?>
                <div class="col-sm-12 well" style="padding-left: 0;">

                    <?php foreach ($files as $file):?>
                        <div class="row">
                            <div class="col-sm-9">
                                <a
                                    href  = "<?php echo $this->createUrl('admin/templates', array('sa'=>'view','screenname'=>$screenname,'templatename'=>$templatename, 'editfile' => $file )); ?>"
                                    class = "<?php if($file == $relativePathEditfile ){echo 'text-danger';}else{echo 'text-success';}; ?>"
                                    >
                                    <?php echo (empty(substr(strrchr($file, DIRECTORY_SEPARATOR), 1)))?$file:substr(strrchr($file, DIRECTORY_SEPARATOR), 1) ;?>
                                </a>
                            </div>
                            <div class="col-sm-3">
                                <?php if ( $oEditedTemplate->getTemplateForFile($file, $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName):?>
                                    <span class="label label-success">
                                        <?php eT("local"); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="label label-danger">
                                        <?php eT("inherited"); ?>
                                    </span>
                                <?php endif;?>
                            </div>
                        </div>
                    <?php endforeach;?>
                </div>
            </div>

            <div>
                <?php eT("JavaScript files:"); ?>
                <div class="col-sm-12 well">

                    <?php foreach ($jsfiles as $file):?>
                        <div class="row">
                            <div class="col-sm-9">
                                <a
                                    href="<?php echo $this->createUrl('admin/templates', array('sa'=>'view','screenname'=>$screenname,'templatename'=>$templatename, 'editfile' => $file )); ?>"
                                    class = "<?php if($file == $relativePathEditfile ){echo 'text-danger';}else{echo 'text-success';}; ?>"
                                >
                                    <?php echo (empty(substr(strrchr($file, DIRECTORY_SEPARATOR), 1)))?$file:substr(strrchr($file, DIRECTORY_SEPARATOR), 1) ;?>
                                </a>
                            </div>
                            <div class="col-sm-3">
                                <?php if ( $oEditedTemplate->getTemplateForFile($file, $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName):?>
                                    <span class="label label-success">
                                        <?php eT("local"); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="label label-danger">
                                        <?php eT("inherited"); ?>
                                    </span>
                                <?php endif;?>
                            </div>
                        </div>
                    <?php endforeach;?>
                </div>
            </div>

            <div >
                <?php eT("CSS files:"); ?>
                <div class="col-sm-12 well">

                    <?php foreach ($cssfiles as $file):?>
                        <div class="row">
                            <div class="col-sm-9">
                                <a
                                    href="<?php echo $this->createUrl('admin/templates', array('sa'=>'view','screenname'=>$screenname,'templatename'=>$templatename, 'editfile' => $file )); ?>"
                                    class = "<?php if($file == $relativePathEditfile ){echo 'text-danger';}else{echo 'text-success';}; ?>"
                                >
                                    <?php echo (empty(substr(strrchr($file, DIRECTORY_SEPARATOR), 1)))?$file:substr(strrchr($file, DIRECTORY_SEPARATOR), 1) ;?>
                                </a>
                            </div>
                            <div class="col-sm-3">
                                <?php if ( $oEditedTemplate->getTemplateForFile($file, $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName):?>
                                    <span class="label label-success">
                                        <?php eT("local"); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="label label-danger">
                                        <?php eT("inherited"); ?>
                                    </span>
                                <?php endif;?>
                            </div>
                        </div>
                    <?php endforeach;?>
                </div>
            </div>
        </div>


        <div class="col-lg-8 templateeditor">
            <?php echo CHtml::form(array('admin/templates/sa/templatesavechanges'), 'post', array('id'=>'editTemplate', 'name'=>'editTemplate')); ?>

            <?php echo CHtml::hiddenField('templatename', $templatename, array('class'=>'templatename'));
            echo CHtml::hiddenField('screenname', $screenname, array('class'=>'screenname'));
            echo CHtml::hiddenField('editfile', $editfile);
            echo CHtml::hiddenField('relativePathEditfile', $relativePathEditfile);
            echo CHtml::hiddenField('action', 'templatesavechanges');

            echo CHtml::textArea('changes', (!empty($editfile))?file_get_contents($editfile):'',array('rows'=>'20',
                'cols'=>'40',
                'data-filetype'=>$sEditorFileType,
                'class'=>'ace '.$sTemplateEditorMode,
                'style'=>'width:100%'
            ));
            ?>
            <p class='text-center'>
                <br/>
                    <?php if (Permission::model()->hasGlobalPermission('templates','update')):?>

                        <?php
                            $sSaveText = ( $oEditedTemplate->getTemplateForFile($relativePathEditfile, $oEditedTemplate)->sTemplateName == $oEditedTemplate->sTemplateName)?gT("Save changes"):gT("Copy to local template and save changes");
                        ?>

                        <?php if (is_writable($templates[$templatename])):?>

                            <input type='submit' class='btn btn-default' value='<?php echo $sSaveText; ?>'
                                <?php if (!is_template_editable($templatename)):?>
                                    disabled='disabled' alt='<?php eT("Changes cannot be saved to a standard template."); ?>'
                                    <?php endif; ?>
                            />
                        <?php endif; ?>

                    <?php endif; ?>
            </p>
            </form>
        </div>



        <div class="col-lg-2" style="overflow-x: hidden">
            <div>
                <?php eT("Other files:"); ?>
                <br/>
                <?php
                echo CHtml::form(array('admin/templates/sa/templatefiledelete'), 'post');
                echo CHtml::listBox('otherfile','',array_combine($otherfiles,$otherfiles),array('size'=>11,'class'=>"form-control")); ?>
                <br>
                <?php
                if (Permission::model()->hasGlobalPermission('templates','delete'))
                { ?>

                    <input type='submit' class='btn btn-default' value='<?php eT("Delete"); ?>' onclick="javascript:return confirm('<?php eT("Are you sure you want to delete this file?","js"); ?>')"/>
                    <?php
                }
                ?>
                <input type='hidden' name='screenname' value='<?php echo htmlspecialchars($screenname); ?>' />
                <input type='hidden' name='templatename' value='<?php echo htmlspecialchars($templatename); ?>' />
                <input type='hidden' name='editfile' value='<?php echo htmlspecialchars($editfile); ?>' />
                <input type='hidden' name='action' value='templatefiledelete' />
                </form>
            </div>
            <div style='margin-top:1em;'>
                <?php
                if (Permission::model()->hasGlobalPermission('templates','update'))
                { ?>

                    <?php echo CHtml::form(array('admin/templates/sa/uploadfile'), 'post', array('id'=>'importtemplatefile', 'name'=>'importtemplatefile', 'enctype'=>'multipart/form-data')); ?>
                    <?php printf(gT("Upload a file (maximum size: %d MB):"),getMaximumFileUploadSize()/1024/1024); ?>
                    <br>
                    <input name='upload_file' id="upload_file" type="file" required="required"/>
                    <input type='submit' value='<?php eT("Upload"); ?>' class='btn btn-default'
                        <?php if (!is_template_editable($templatename)) : ?>
                            disabled='disabled'
                            <?php endif; ?>
                        />
                    <input type='hidden' name='editfile' value='<?php echo $editfile; ?>' />
                    <input type='hidden' name='screenname' value='<?php echo HTMLEscape($screenname); ?>' />
                    <input type='hidden' name='templatename' value='<?php echo $templatename; ?>' />
                    <input type='hidden' name='action' value='templateuploadfile' />
                    </form>
                    <?php
                }
                ?>
            </div>

            <div class="">
                <a href="#" data-toggle="modal" data-target="#fileHelp" />
                <?php eT('Tip: How to embed a picture in your template?'); ?>
                </a>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="fileHelp" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel"><?php eT('Tip: How to display a picture in your template?'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <?php eT('To use a picture in a .pstpl file:');?><br/>
                        <code>&lt;img src="{TEMPLATEURL}files/yourpicture.png" /&gt;</code><br/><br/>
                        <?php eT("To use a picture in a .css file: ");?><br/>
                        <code>background-image: url('../files/yourpicture.png');</code><br/><br/>
                        <?php eT("To place the logo anywhere in a .pstpl file: ");?><br/>
                        <code>{SITELOGO}</code><br/>
                        <?php eT("This will generate a responsive image containing the logo file.");?><br/><br>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php eT("Close");?></button>
                    </div>
                </div>
            </div>
        </div>



    </div>
    </div>
    <?php endif;?>

<div class="row template-sum" style="margin-bottom: 100px;">
    <div class="col-lg-12">
        <h4>
            <?php eT("Preview:"); ?>
        </h4>
        <div class="jumbotron message-box">
            <input type='button' value='<?php eT("Mobile"); ?>' id='iphone' class="btn btn-default"/>
            <input type='button' value='640x480' id='x640' class="btn btn-default" />
            <input type='button' value='800x600' id='x800' class="btn btn-default" />
            <input type='button' value='1024x768' id='x1024' class="btn btn-default" />
            <input type='button' value='<?php eT("Full"); ?>' id='full' class="btn btn-default" />
            <br><br><br><br>

            <div style='width:90%; margin:0 auto;'>
                <?php if(isset($filenotwritten) && $filenotwritten==true)
                { ?>
                    <p>
                        <span class ='errortitle'><?php echo sprintf(gT("Please change the directory permissions of the folder %s in order to preview templates."), $tempdir); ?></span>
                    </p>
                </div>
                <?php }
            else
            { ?>
                <p>
                    <iframe id='previewiframe' src='<?php echo $this->createUrl('admin/templates/sa/tmp/',array('id'=>$time)); ?>' height='768' name='previewiframe' style='width:95%;background-color: white;'>Embedded Frame</iframe>
                </p>
            </div>
            <?php
        } ?>
    </div>
</div>
</div>

<?php if (!defined('THINK_PATH')) exit();?><div class="row-fluid"><div class="span12"><form method='post' name="login" action="__URL__/codys/"><table class="table-striped table-condensed flip-content" style="margin-top:100px; margin-left:auto; margin-right:auto;"><tr><td><?php echo xstr('second_password');?>：</td><td><input type="hidden" name="Urlsz" value="<?php echo ($vo['c_id']); ?>" /><input type="password" title="<?php echo xstr('please_input_second_password');?>" name="oldpassword"></td><td><input name="submit"  type="submit" value="<?php echo xstr('confirm2');?>" alt="<?php echo xstr('confirm');?>"  class="btn red" /></td></tr></table></form></div></div>
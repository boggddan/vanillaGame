<!--
[name: !Вызов замерщика]
[type: Информация]
-->
<div class="page request">
  <div class="head-item">
    <div class="title">Вызов мастера замерщика :</div>
    <div class="clear"></div>
  </div>
    <span class="constructor-template" data-template-type="interface" data-template-id="34"><?php echo General::getInterface(34); ?></span>
  <div class="feedback">

    <?php
if (isset($success))
{
  // echo '<div class="mailsend constructor-template" data-template-type="interface" data-template-id="">Сообщение успешно отправлено</div>';
?>
  <script>
    window.location.assign('/success');

    // $(function() {
    //     $( "#dialog-message" ).dialog({
    //         modal: true,
    //         buttons: {
    //             Ok: function() {
    //                 $( this ).dialog( "close" );
    //             }
    //         }
    //     });
    // });
    </script>
    <!-- <div id="dialog-message" title="Обратная связь">Сообщение успешно отправлено</div> -->
    <?php
}
    ?>
    <form id="callform" name="callform" method="post" action="">
      <fieldset class="contact-form">
        <div class="input-block">
          <label>Контактное лицо:</label>
          <div class="input-wrap border-r"><input type="text" class="fio border-r" name="fio" /></div>
          <div class="clear"></div>
        </div>
        <div class="input-block">
          <label>E-mail:</label>
          <div class="input-wrap border-r"><input type="text" class="email border-r" name="email" /></div>
          <div class="clear"></div>
        </div>
        <div class="input-block">
          <label>Телефон:</label>
          <div class="input-wrap border-r"><input type="text" class="border-r" name="phone" /></div>
          <div class="clear"></div>
        </div>
        <div class="input-block">
          <label>Ваше сообщение:</label>
          <div class="input-wrap border-r"><textarea class="border-r" name="text"></textarea></div>
          <div class="clear"></div>
        </div>
        <div class="input-block captcha">
          <label>Введите код:</label>
          <?php echo $captcha; ?>
          <div class="input-wrap border-r"><input type="text" class="border-r" name="captcha"  /></div>
          <div class="clear"></div>
        </div>
        <a href="#" class="select-button right brown border-r send">Отправить
          <div class="arrow-right"></div>
          <div class="close"></div>
        </a>
      </fieldset>
    </form>
  </div>
  <script>
    $(document).on('click', '.send', function(e) {
      e.preventDefault();

      var text = $('.contact-form textarea').val(),
          fio = $('.contact-form .fio').val(),
          email = $('.contact-form .email').val(),
          status = true;

      if (text.length == 0) {
        status = false;
        $('.contact-form textarea').focus();
        alert('Введите сообщение, которое вы хотите отправить нам');
      }
      else if (fio.length == 0) {
        status = false;
        $('.contact-form .fio').focus();
        alert('Укажите, пожалуйста, ваше имя и инициалы');
      }
      else if (!(/^([a-z0-9_\-]+\.)*[a-z0-9_\-]+@([a-z0-9][a-z0-9\-]*\.)+[a-z]{2,4}$/i).test(email)) {
        status = false;
        $('.contact-form .email').focus();
        alert('Укажите, пожалуйста, коректный email адресс');
      }
      /**************************/

       if (status) {
      var captcha = document.callform.captcha.value;
      $.ajax({
        url: '/tpl/templates_master',
        type: 'post',
        data: { 'recaptcha' : 'true', 'captcha' : captcha},
        success: function(res) {
          if (res == 'ok')
          {
           $('#callform').submit();
          }
          else
          {
            alert("Введите капчу");
            status = false;
            var idca = Math.floor(Math.random()*1000000);
            $('.capcha img').attr('src', '/captcha/default?id='+idca);
          }
        }
      });
    }

      /**************************/

    });
  </script>
  <div class="request-contacts">
    <div class="request-contacts-wrap">
 <span class="constructor-template" data-template-type="interface" data-template-id="33"><?php echo General::getInterface(33); ?></span>
  </div>
</div>
</div>

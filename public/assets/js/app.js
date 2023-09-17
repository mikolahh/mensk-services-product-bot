
$(function () {
  let webAppObj = window.Telegram.WebApp  
  webAppObj.expand()
  const bgColor = webAppObj.themeParams.bg_color 
  const textColor = webAppObj.themeParams.text_color
  const linkColor = webAppObj.themeParams.link_color
  const  buttonColor = webAppObj.themeParams.button_color
  const  buttonTextColor = webAppObj.themeParams.button_text_color
    
   const initData  = webAppObj.initDataUnsafe
   $('body').css({backgroundColor: bgColor, color: textColor})
   $('a').css({color: linkColor})
   $('button').css({backgroundColor: buttonColor, color: buttonTextColor})

  /* const queryData = {
    firstName: 'Mikola',
    lastName: 'Pitersky',   
  } */
  
  /* $.ajax({
    method: "POST",
    url: "https://php-draft.mikalay.tech/bot/web",      
    data: JSON.stringify(queryData),
    contentType: 'application/json',
    dataType: 'json',
    success: function (response) {
      let resp = response
      // console.log(resp)
    }
  }) */
   

 // Реакция на нажатие кнопки с пользователем
  $(".users__list button").click(function (e) {   
      // Получаем необходимую нам информацию (clientId) и записываем ее в глобальную переменную
    window.clientId = $(e.target).attr('name')    
    if (webAppObj.MainButton.isVisible) {
      webAppObj.MainButton.hide()
    } else {
      webAppObj.MainButton.setText(`Выбрать пользователя с Id: ${clientId}`)
      webAppObj.MainButton.show()
    }    
  })
  Telegram.WebApp.onEvent("mainButtonClicked", function(){    
    const queryData = {
      web_app_query: {
        init_data: initData,
        data: {          
          client_id: clientId      
        }
      }
    }
    $.ajax({
      method: "POST",
      url: "https://product-bot.mikalay.tech/bot",      
      data: JSON.stringify(queryData),
      contentType: 'application/json',
      dataType: 'json',
      success: function (response) {
        let resp = response
        // console.log(resp)
      }
    }) 
    // webAppObj.sendData(clientId) 
      
  })


})



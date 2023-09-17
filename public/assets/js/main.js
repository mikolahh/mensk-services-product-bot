
// Запуск webApp
$(function () {
  let webAppObj = window.Telegram.WebApp 
  let version = webAppObj.version
  let platform = webAppObj.platform
  let initData = webAppObj.initData
  let initDataUnsafe = webAppObj.initDataUnsafe
  let colorScheme = webAppObj.colorScheme
  let themeParams = webAppObj.themeParams
  let isExpanded = webAppObj.isExpanded
  let viewportHeight = webAppObj.viewportHeight
  let MainButton = webAppObj.MainButton
  let BackButton = webAppObj.BackButton
  // MainButton.show()
  // BackButton.show()  
  // MainButton.setText('Main')
  // BackButton.setText('до свидания')
  // console.log(MainButton)
  // console.log(MainButton.isVisible)  
  // console.log(BackButton.isVisible)
  $.ajax({
    method: "POST",
    url: "web",
    data: {
      version: version,
      platform: platform,
      initData: initData,
      initDataUnsafe: initDataUnsafe,
      colorScheme: colorScheme,
      themeParams: themeParams,
      isExpanded: isExpanded,
      viewportHeight: viewportHeight,
      // MainButton: MainButton
    },
    success: function (response) {
      let resp = response
      console.log(resp)
    }
  })
      // Реакция на нажатие кнопки с пользователем
  $(".users__list button").click(function (e) {
    
    let userId = $(e.target).attr('name')    
       
  })
})






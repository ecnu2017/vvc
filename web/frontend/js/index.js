let character

let login = () => {
  document.getElementById("form").submit();
  /*
  let username = document.getElementById('username').value
  let password = document.getElementById('password').value
  let captcha = document.getElementById('captcha').value
  if (!username || !password) {
    window.alert('请输入用户名和密码！')
    return
  }

  if (!captcha) {
    window.alert('请输入验证码！')
    return
  }

  if (username !== 'admin' && username !== 'user') {
    window.alert('用户名不存在')
    return
  }

  if (password !== 'admin') {
    window.alert('密码错误，请检查并重试！')
    return
  }

  window.alert('登录成功！')
  if (username === 'admin') {
  window.location.href = './navi-admin.html'
    return
  }
  window.location.href = './navi-guest.html'
  */
}

let logout = () => {
  window.alert('注销成功，谢谢使用！')
  window.location.href = './index.html'
}

let zhinengxuexi = (char) => {
  if(char === 'admin') {
    character = 'admin'
  } else {
    character = 'guest'
  }
  window.location.href = './studyJob.html'
}

let juesebanyan = () => {
  window.location.href = './rpg.html'
}

let goback2 = () => {
  window.location.href = './studyJob.html'
}

// simples handlers
document.addEventListener('click', function(e){
  if(e.target.classList.contains('like-btn')){
    let postId = e.target.dataset.post;
    fetch('/ajax/like.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'post_id='+encodeURIComponent(postId)})
      .then(r=>r.json()).then(data=>{
        if(data.error) alert(data.error);
        else e.target.textContent = (data.status==='liked')? 'Descurtir':'Curtir';
      });
  }
});

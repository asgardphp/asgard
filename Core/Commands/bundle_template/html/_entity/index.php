<% foreach($<?=$entity['meta']['plural'] ?> as $<?=$entity['meta']['name'] ?>): %>
<a href="<%=$this->url('show', ['id'=>$<?=$entity['meta']['name']?>->id])%>"><%=$<?=$entity['meta']['name']?>%></a><br>
<% endforeach; %>

<%=$paginator->render()%>
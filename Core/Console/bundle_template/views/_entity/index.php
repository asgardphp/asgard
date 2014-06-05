<% foreach($<?php echo $entity['meta']['plural'] ?> as $<?php echo $entity['meta']['name'] ?>): %>
<a href="<% echo $this->url_for('show', array('id'=>$<?php echo $entity['meta']['name'] ?>->id)) ?>"><% echo $<?php echo $entity['meta']['name'] ?> %></a><br>
<% endforeach; %>

<% echo $paginator->render() %>
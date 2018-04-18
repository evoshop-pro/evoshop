<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>EvoShop</title>
    <link rel="stylesheet" type="text/css" href="media/style/default/style.css">
    <link rel="stylesheet" href="media/style/common/font-awesome/css/font-awesome.min.css">
    <script type="text/javascript" src="media/script/tabpane.js"></script>
</head>
<body>
	<h1 class="pagetitle">
			<span class="pagetitle-icon">
	    <i class="fa fa-shopping-bag"></i>
	  </span>
		<span class="pagetitle-text">
		    EvoShop
	    </span>
	</h1>
	<div class="sectionBody" id="andyShopPane">
		<div class="tab-pane">
			<script type="text/javascript">
		        tpResources = new WebFXTabPane( document.getElementById( "andyShopPane" ), true);
		    </script>
		</div>
		<div class="tab-page" id="Settings">
	        <h2 class="tab"><i class="fa fa-newspaper-o"></i>Настройки магазина</h2>
	        <script type="text/javascript">tpResources.addTabPage( document.getElementById( "Settings" ) );</script>
	        
	        <table class="formconfig">
	        	
	        	[+main.settings+]

	        </table>
		</div>
		<div class="tab-page" id="Orders">
	        <h2 class="tab"><i class="fa fa-shopping-bag"></i>Заказы</h2>
	        <script type="text/javascript">tpResources.addTabPage( document.getElementById( "Orders" ) );</script>
			[+main.orders+]
		</div>
    </div>
</body>
</html>
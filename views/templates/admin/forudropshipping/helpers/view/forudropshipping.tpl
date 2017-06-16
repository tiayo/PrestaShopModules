{*
* 2017-2018 Zheng xiang jing
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to it@mg.forudropshipping.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize Forudropshipping for your
* needs please refer to http://www.forudropshipping.com for more information.
*
*  @author Zheng xiang jing<it@mg.forudropshipping.com>
*  @copyright  2017-2018 Zheng xiang jing
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<html>
<head>
<script src="http://cdn.bootcss.com/flatpickr/2.4.3/flatpickr.min.js"></script>
<script src="http://cdn.bootcss.com/jquery/3.2.0/jquery.min.js"></script>
<link type="text/css" href="http://cdn.bootcss.com/flatpickr/2.4.3/flatpickr.min.css">
<script>
	$(document).ready(function () {
	    html = '<img src="/modules/forudropshipping/logo.png" width="300" />'
		$('.page-title').html(html);
        $('#m-callback-start').click(function() {
            html = '<div class="bgc"></div>\
                    <div class="float">\
                    <a href="#" id="cancel_import" onclick="window.history.go(0);return false;" class="bgc_close">X</a>\
                    <h2>product import process </h2>\
						<div class="bs-example m-callback">\
							<div class="progress"><div class="progress-bar" role="progressbar" data-transitiongoal-backup="100">\
								</div>\
							</div>\
							<p>progress: <span class="label label-info" id="m-callback-update"></span></p>\
							<p>done: <span class="label label-success" id="m-callback-done"></span></p>\
						</div>\
                    </div>\
                ';
            $('#content').append(html);
            setTimeout("Push()",0);
            setInterval("Push()",3000);
        });
    })

    function Push() {
        $.ajax({
            type: "get",
            url: "/modules/forudropshipping/src/Product/record.php",
            dataType: "json",
            success: function (data) {
                var $pb = $('.m-callback .progress-bar');
                $pb.attr('data-transitiongoal', data['value']);
                $pb.attr('aria-valuenow', data['value']);
                $pb.attr('style', 'width:' + data['value'] + '%');
                $('#m-callback-update').html(data['value']);
                $('#m-callback-done').html(data['prompt']);

            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                alert(errorThrown);
            }
        });
    }
</script>

<style>
	.bootstrap .form-control, .bootstrap input[type="text"], .bootstrap input[type="search"], .bootstrap input[type="password"], .bootstrap textarea, .bootstrap select{
		width: 40%;
	}
	.bootstrap .page-head h2.page-title{
		padding: 0.5em 0 0 230px;
	}
	.bgc{
		width: 100%;
		height: 100%;
		float: left;
		position: fixed;
		top: 0;
		left: 0;
		background: rgba(0,0,0,0.7);
	}
	.float{
		width: 35%;
		float: left;
		background:#fff;
		position: fixed;
		padding: 1em;
		top:0;
		border: 1px solid #ccc;
		border-radius: 5px;
		margin:15% 0 0 25%;
	}
	.bgc_close{
		float: right;
	}
	.progress {
		display: -webkit-box;
		display: -webkit-flex;
		display: -ms-flexbox;
		display: flex;
		overflow: hidden;
		font-size: .75rem;
		line-height: 1rem;
		text-align: center;
		background-color: #eceeef;
		border-radius: .25rem;
	}
	.progress-bar {
		height: 1rem;
		color: #fff;
		background-color: #0275d8;
	}
	.panel p{
		width: 100%;
		line-height:1em;
		margin:1em 0 0 0;
		padding: 0;
		float: left;
	}
</style>
</head>

<body>
{if $message != null}
	{if $status == 1}
		<div class="panel" style="width:98%; float:left;border-left:solid 3px #92d097;background-color:#ddf0de;color:#72C279">
	{/if}
	{if $status == 2}
		<div class="panel" style="width:98%; float:left;border-left:solid 3px #eab3b7;background-color:#FFE2E4;color:#D27C82">
	{/if}
			<p style="margin:0.5em 0 0.5em 0; font-size: 1.2em;">{{$message|escape:'htmlall':'UTF-8'}}</p>
	</div>
{/if}

<div class="panel" style="width:98%; float:left;">
	<h2>Export Order</h2>
		<form action="{{$links|escape:'htmlall':'UTF-8'}}" method="post" class="bs-docs-example">
			<input type="hidden" name="page" value="export_all_order">
			<input type="submit" value="Start export" class="btn btn-large btn-primary">
		</form>

	<p style="height:2em;"></p>

	<h2>Export Order by date</h2>
	<form class="form-signin" action="{{$links|escape:'htmlall':'UTF-8'}}" method="post">
		<input type="hidden" name="page" value="export_order_by_date">
		<input type="hidden" name="order" value="date">
		<input class="flatpickr" id="range" type="text" name="date" placeholder="Select Date..">
		<p></p>
		<input type="submit" class="btn btn-large btn-primary" value="Confirm export">
	</form>
	<script>
        window.onload = function () {
            flatpickr("#range", {
                "mode": "range",
                enableTime: true,
                altInput: true,
                altFormat: "Y-m-d H:i:S"
            });
        }
	</script>
</div>

<div class="panel" style="width:98%; float:left;">
	<h2 style="">Import Product</h2>
	<form action="{{$links|escape:'htmlall':'UTF-8'}}" method="post" class="bs-docs-example" enctype="multipart/form-data">
		<input type="hidden" name="page" value="import_product">
		<input type="file" name="product" class="filestyle" style="float:left;">
		<div style="width: 100%;float:left;margin: 1em 0 1em;">
			<select name="category" class="form-control" style=" width:40%; float:left;">
				<option value="2">Select Category...</option>
                {{$category|escape:'htmlall':'UTF-8'}}
			</select>
			<a style="float:left;margin-left: 2em;" href="{{$adminCategories|escape:'htmlall':'UTF-8'}}" class="btn btn-large btn-primary">Add category</a>
		</div>
		<input type="submit" value="Start Import" class="btn btn-large btn-primary" id="m-callback-start" style="float:left;">
		<a href="/modules/forudropshipping/src/Product/products.csv" id="import_product_button" style="float:left; margin:0.8em 0 0 2em;">Sample download</a>
	</form>
</div>

<div class="panel" style="width:98%; float:left;">
	<h2>Import Tracking</h2>
	<form action="{{$links|escape:'htmlall':'UTF-8'}}" method="post" class="bs-docs-example" enctype="multipart/form-data">
		<input type="hidden" name="page" value="import_tracking">
		<input type="file" name="tracking" class="btn" style="float: left;">
		<input type="submit" value="Start Import" class="btn btn-large btn-primary" style="float:left;">
		<a href="/modules/forudropshipping/src/Tracking/tracking.csv" id="import_product_button" style="float:left; margin:0.8em 0 0 2em;">Sample download</a>
	</form>
</div>

<div class="panel" style="width:98%; float:left;">
	<h2>API</h2>

	<form action="{{$links|escape:'htmlall':'UTF-8'}}" method="post"
		  class="bs-docs-example"
		  enctype="multipart/form-data"
		  id="create_token_form"
		  style="display: {if !empty($api_key)} none {/if}"
	>
		<input type="hidden" name="page" value="api_key">
		<div class="form-group">
			<label class=" control-label">Username<span class="required" aria-required="true">*</span></label>
			<input type="text" class="form-control" name="username" value="" required="" aria-required="true">
		</div>
		<div class="form-group">
			<label class=" control-label">Password<span class="required" aria-required="true">*</span></label>
			<input type="password" class="form-control" name="password" value="" required="" aria-required="true">
		</div>
		<input type="submit" value="Create Token" class="btn btn-large btn-primary" style="float:left; margin: 1em 0">
	</form>

	<form style="width: 100%;float:left;display:{if empty($api_key)} none {/if}" id="view_token_form">
		<div class="form-group">
			<input type="text" class="form-control" value="{{$api_key|escape:'htmlall':'UTF-8'}}" aria-required="true" readonly>
		</div>
		<input type="button" value="Refresh Token" class="btn btn-large btn-primary" style="float:left;" id="view_token_form_submit">
	</form>

	<p>Entry token listed on <a href="https://www.forudropshipping.com/stores">https://www.forudropshipping.com/stores</a></p>
	<p>Please set this operation under the guidance of the developer!</p>
</div>
<script>
	$(document).ready(function (){
        $('#view_token_form_submit').click(function () {
            $('#create_token_form')[0].style.display = 'block';
            $('#view_token_form')[0].style.display = 'none';
        });
    });
</script>
</body>
</html>
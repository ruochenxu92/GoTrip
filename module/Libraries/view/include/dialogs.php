<div id="confirmDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"
					aria-hidden="true">&times;</button>
				<h4 class="modal-title">
					请您确认
				</h4>
			</div>
			<div class="modal-body">
				<div name="msgDiv">
					<div name="word">
						<span></span>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<form class="form-inline">
					<button name="cancel" class="btn">
						取消
					</button>
					<button name="confirm" class="btn btn-primary">
						确认
					</button>
				</form>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<div id="inputDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"
					aria-hidden="true">&times;</button>
				<h4 class="modal-title">
					需要更多信息
				</h4>
			</div>
			<div class="modal-body">
				<div name="msgDiv">
					<div name="word">
						<span></span>
					</div>
					<div name="input">
						<form>
							<input class="form-control" name="input" type="text" />
						</form>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<form class="form-inline">
					<button name="cancel" class="btn">
						取消
					</button>
					<button name="confirm" class="btn btn-primary">
						确定
					</button>
				</form>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<div id="infoDialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"
					aria-hidden="true">&times;</button>
				<h4 class="modal-title">
					信息
				</h4>
			</div>
			<div class="modal-body">
				<div name="msgDiv">
					<div name="word">
						<span></span>
					</div>
					<div name="loadingBar"></div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true">
					关闭
				</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<script type="text/javascript">
	$(document).ready(function()
	{
		$("#infoDialog").on("hide.bs.modal", function()
		{
			if ($info_close_function != undefined)
			{
				$info_close_function();
			}
		});
		$("#confirmDialog div.modal-footer > form > button[name='cancel']").bind("click", function()
		{
			if ($confirm_cancel_function != undefined)
			{
				$confirm_cancel_function();
			}
			$("#confirmDialog").modal("hide");
			return false;
		});
		$("#confirmDialog div.modal-footer > form > button[name='confirm']").bind("click", function()
		{
			if ($confirm_ok_function != undefined)
			{
				$confirm_ok_function();
			}
			$("#confirmDialog").modal("hide");
			return false;
		});
		$("#inputDialog div.modal-footer > form > button[name='cancel']").bind("click", function()
		{
			if ($input_cancel_function != undefined)
			{
				$input_cancel_function();
			}
			$("#inputDialog").modal("hide");
			return false;
		});
		$("#inputDialog div.modal-footer > form > button[name='confirm']").bind("click", function()
		{
			if ($input_ok_function != undefined)
			{
				$input_ok_function($("#inputDialog div.modal-body > div[name='msgDiv'] > div[name='input'] input[name='input']").val());
			}
			$("#inputDialog").modal("hide");
			return false;
		});
	});
	
	function showInfoDialog(text, info_close_function)
	{
		$words_span = $("#infoDialog div.modal-body > div[name='msgDiv'] > div[name='word'] > span");
		
		text = text.replace(/@success/g, "操作已成功");
		text = text.replace(/@error/g, "操作失败");
		text = text.replace(/@denied/g, "操作被拒绝");
		text = text.replace(/@operating/g, "操作正在进行中");
		
		$words_span.text(text);
		$info_close_function = info_close_function;
		$("#infoDialog").modal("show");
	}
	
	function showConfirmDialog(text, ok_function, cancel_function)
	{
		$words_span = $("#confirmDialog div.modal-body > div[name='msgDiv'] > div[name='word'] > span");
		
		text = text.replace(/@success/g, "操作已成功");
		text = text.replace(/@error/g, "操作失败");
		text = text.replace(/@denied/g, "操作被拒绝");
		text = text.replace(/@operating/g, "操作正在进行中");
		
		$words_span.text(text);
		$confirm_ok_function = ok_function;
		$confirm_cancel_function = cancel_function;
		
		$("#confirmDialog").modal("show");
	}
	
	function showInputDialog(text, ok_function, cancel_function)
	{
		$words_span = $("#inputDialog div.modal-body > div[name='msgDiv'] > div[name='word'] > span");
		$input = $("#inputDialog div.modal-body > div[name='msgDiv'] > div[name='input'] input[name='input']");

		text = text.replace(/@success/g, "操作已成功");
		text = text.replace(/@error/g, "操作失败");
		text = text.replace(/@denied/g, "操作被拒绝");
		text = text.replace(/@operating/g, "操作正在进行中");
		
		$words_span.text(text);
		$input.val("");
		$input_ok_function = ok_function;
		$input_cancel_function = cancel_function;
		
		$("#inputDialog").modal("show");
	}
</script>
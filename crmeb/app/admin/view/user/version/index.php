{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('addVersion')}',{h:700,w:1100})">添加版本</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">编号</th>
                            <th class="text-center">版本</th>
                            <th class="text-center">升级类型</th>
                            <th class="text-center">提示内容</th>
                            <th class="text-center">升级连接</th>
                            <th class="text-center">版本号</th>
                            <th class="text-center">操作</th>
                        </tr>
                        </thead>
                        <tbody class="">
                        {volist name="list" id="vo"}
                        <tr>
                            <td class="text-center">
                                {$vo.id}
                            </td>
                            <td class="text-center">
                                <?php if(!$vo['status'] == 1){ ?>
                                <span class="label label-warning">安卓</span>
                                <?php }else{ ?>
                                <span class="label label-danger">苹果</span>
                                <?php }?>
                            </td>
                            <td class="text-center">
                                <?php if(!$vo['type'] == 1){ ?>
                                    <span class="label label-warning">提示升级</span>
                                <?php }else{ ?>
                                    <span class="label label-danger">强制升级</span>
                                <?php }?>
                            </td>
                            <td class="text-center">
                                {$vo.content}
                            </td>
                            <td class="text-center">
                                {$vo.url}
                            </td>
                            <td class="text-center">
                                {$vo.version}
                            </td>
                            <td class="text-center">
                                <button class="btn btn-primary btn-xs" type="button"  onclick="$eb.createModalFrame('编辑','{:Url('edit',array('id'=>$vo['id']))}',{w:400,h:170})"><i class="fa fa-paste"></i> 编辑</button> </td>
                        </tr>
                        {/volist}
                        </tbody>
                    </table>
                </div>
                {include file="public/inner_page"}
            </div>
        </div>
    </div>
</div>
<script>
</script>
{/block}

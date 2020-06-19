{extend name="public/container"}
{block name="content"}
<div class="row">
    <div class="col-sm-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <div class="m-b m-l">
                        <button class="layui-btn layui-btn-sm" onclick="$eb.createModalFrame(this.innerText,'{:Url('addMsg')}',{h:700,w:1100})">添加通知</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped  table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">编号</th>
                            <th class="text-center">通知标题</th>
                            <th class="text-center">通知内容</th>
                            <th class="text-center">添加时间</th>
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
                                {$vo.title}
                            </td>
                            <td class="text-center">
                                {$vo.content}
                            </td>
                            <td class="text-center">
                                {$vo.add_time}
                            </td>
                            <td class="text-center">
                                <button class="btn btn-primary btn-xs" type="button"  onclick="$eb.createModalFrame('编辑','{:Url('editMsg',array('id'=>$vo['id']))}',{w:400,h:170})"><i class="fa fa-paste"></i> 编辑</button>
<!--                                <button  style="margin-top: 5px;" class="btn btn-warning btn-xs del_news_one" data-id="{$vo.id}" type="button" data-url="{:Url('delMsg',array('id'=>$vo['id']))}" ><i class="fa fa-warning"></i> 删除</button>
-->                            </td>
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
    $('.del_news_one').on('click',function(){
        window.t = $(this);
        var _this = $(this),url =_this.data('url');
        $eb.$swal('delMsg',function(){
            $eb.axios.get(url).then(function(res){
                console.log(res);
                if(res.status == 200 && res.data.code == 200) {
                    $eb.$swal('success',res.data.msg);
                    _this.parents('tr').remove();
                }else
                    return Promise.reject(res.data.msg || '删除失败')
            }).catch(function(err){
                $eb.$swal('error',err);
            });
        })
    });
</script>
{/block}

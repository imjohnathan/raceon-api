<script type="text/javascript" src="{{ URL::asset('assets/scripts/ace/ace.js') }}"></script>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-1 mb-2">
        <h2>Edit</h2>
        <div>
        <button form="edit" type="submit"  class="btn btn-primary">儲存</button>
        <a href="{{ strtok($_SERVER['REQUEST_URI'], '?') }}"  class="btn btn-secondary">返回</a></button>
        </div>
</div>
<form id="edit" method="post" action="{{$_SERVER['REQUEST_URI']}}">
    <div class="form-check">
        <input class="form-check-input" id="source" name="source" type="checkbox" value="cyberbiz">
        <label class="form-check-label" for="source">
          Cyberbiz Format
        </label>
      </div>
    <textarea name="editor" data-editor="json" rows="50" style="width:100%"><?php 
     if (isset($data['_links'])) {
        unset($data['_links']);
        foreach ($data['variants'] as $k => $v) {
         unset($data['variants'][$k]['_links']);
        }
    }  
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT); ?></textarea>

</form>   

<script>
    // Hook up ACE editor to all textareas with data-editor attribute
    $(function () {
        $('textarea[data-editor]').each(function () {
            var textarea = $(this);

            var mode = textarea.data('editor');

            var editDiv = $('<div>', {
                position: 'absolute',
                width: textarea.width(),
                height: textarea.height(),
                'class': textarea.attr('class')
            }).insertBefore(textarea);

            textarea.css('visibility', 'hidden');

            var editor = ace.edit(editDiv[0]);
            editor.renderer.setShowGutter(true);
            editor.getSession().setValue(textarea.val());
            editor.getSession().setMode("ace/mode/" + mode);
            editor.setTheme("ace/theme/monokai");
            editor.getSession().setUseWrapMode(true);
            
            // copy back to textarea on form submit...
            textarea.closest('form').submit(function () {
                textarea.val(editor.getSession().getValue());
            })

        });
    });
</script>
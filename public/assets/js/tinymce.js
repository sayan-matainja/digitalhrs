$(function() {
  'use strict';

  //Tinymce editor
  if ($("#tinymceExample").length) {
    tinymce.init({
      selector: '#tinymceExample',
      height: 400,
      menubar: false,
      default_text_color: 'red',
      plugins: [
          'a11ychecker','advlist','advcode','advtable','autolink','checklist','export',
          'lists','link','charmap','anchor','searchreplace','visualblocks',
          'powerpaste','fullscreen','formatpainter','table',
      ],
      toolbar1: 'undo redo | formatpainter casechange blocks | bold italic  | \' +\n' +
          '\'alignleft aligncenter alignright alignjustify | \' +\n' +
          '\'bullist numlist checklist \'',

      // toolbar2: 'print preview media | forecolor backcolor emoticons | codesample help',
      image_advtab: true,
      templates: [{
          title: 'Test template 1',
          content: 'Test 1'
        },
        {
          title: 'Test template 2',
          content: 'Test 2'
        }
      ],
      content_css: []
    });
  }

});

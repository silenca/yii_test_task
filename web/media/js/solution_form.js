var SolutionForm = function (params) {
    this._options = params.options || [];
    this._wrapper = params.wrapper || null;
    this._close = params.close || false;
    this._isOpen = false;
    this._id = null;
    this._html;
    this._onSolution = params.onSolution || null;
};

SolutionForm.prototype.init = function () {
    this.render();
    this.setOptions();
    this.initEvents();
    this._isOpen = true;
    return this._html;
};

SolutionForm.prototype.initEvents = function () {
    this._wrapper.off();
    this._wrapper.children().off();
    this.onSend();
    if (this._close) {
        this.onClose();
    }
};

SolutionForm.prototype.render = function () {
    this._html = $([
        "<div class='panel panel-default'>",
        "   <div class='panel-heading'>",
        "       <div class='panel-title'>Решение</div>",
        "   </div>",
        "   <div class='panel-body'>",
        "       <div class='row'>",
        "           <div class='form-group cs-select-container'>",
        "               <select class='cs-select cs-skin-slide' data-init-plugin='cs-select'>",
        "               </select>",
        "           </div>",
        "       </div>",
        "       <div class='row'>",
        "           <div class='form-group'>",
        "               <textarea rows='6' cols='10' placeholder='Комментарий' class='form-control'></textarea>",
        "           </div>",
        "       </div>",
        "       <div class='row'>",
        "           <div class='form-group right-block'>",
        "               <button class='btn btn-complete send-solution'>Отправить</button>",
        "           </div>",
        "       </div>",
        "   </div>",
        "</div>"
    ].join("\n"));
    if (this._close) {
        this._html.find('.panel-heading').append('<button type="button" class="close" aria-hidden="true"><i class="pg-close fs-14"></i></button>');
    }
    if (this._wrapper) {
        $(this._wrapper).html(this._html);
    }
};

SolutionForm.prototype.setOptions = function () {
    var $select = this._html.find('.cs-select');
    for (var key in this._options) {
        $select.append("<option value='" + key + "'>" + this._options[key] + "</option>");
    }
};

SolutionForm.prototype.setId = function (id) {
    this._id = id;
};

SolutionForm.prototype.onSend = function () {
    var self = this;
    $(this._wrapper).on('click', 'button.send-solution', function () {
        //alert('hello');
        var solution = self._wrapper.find('select option:selected').val();
        self.solution(self._id, solution);
    });
};

SolutionForm.prototype.onClose = function () {
    var self = this;
    $(this._wrapper).on('click', 'button.close', function () {
        self.close();
    });
};

SolutionForm.prototype.solution = function (id, solution) {
    var self = this;
    var comment = this._wrapper.find('textarea').val();
    var data = {
        type: solution,
        comment: comment,
        id: id,
        _csrf: _csrf
    };
    $.post("/contracts/solution", data, function (response) {
        var result = $.parseJSON(response);
        if (result.status === 200) {
            self.close();
            if (self._onSolution) {
                self._onSolution(result.data.solution);
            }
        }

    });
};

SolutionForm.prototype.close = function () {
    $(this._wrapper).empty();
    $(this._wrapper).off();
    this._isOpen = false;
};
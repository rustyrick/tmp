<script>
    var services_array = {{services|json_encode|raw}};
</script>
<h1>{{ jtext('COM_PBBOOKING_EVENT_CREATE') }}</h1>
<form class="form form-horizontal" method="POST" action="" id="mod_pbbmanage_form">
    <div class="row-fluid">
        <div class="span6">
            {% for field in customfields %}
                {{field.getControlGroup|raw}}
            {% endfor %}
        </div>
        <div class="span6">
            

            <div class="control-group">
                <label class="control-label">{{jtext('COM_PBBOOKING_SERVICE_LABEL')}}</label>
                <div class="controls">
                    <select name="service_id">
                        <option value="">{{jtext('JGLOBAL_SELECT_AN_OPTION')}}</option>
                        {% for service in services%}
                            <option value="{{service.id}}">{{service.name}}</option>
                        {%endfor%}
                    </select>
                </div>
            </div>
            
            <div class="control-group">
                <label class="control-label">{{jtext('COM_PBBOOKING_BLOCK_START')}}</label>
                <div class="controls">
                    <input type="text" class="input-normal dtpicker" aria-required="true" name="dtstart"/>
                </div>
            </div>



            <div class="control-group">
                <label class="control-label">{{jtext('COM_PBBOOKING_BLOCK_END')}}</label>
                <div class="controls">
                    <input type="text" class="input-normal dtpicker" aria-required="true" name="dtend"/>
                </div>
            </div>




            <div class="control-group">
                <label class="control-label">{{jtext('COM_PBBOOKING_CAL_LABEL')}}</label>
                <div class="controls">
                    <select name="cal_id">
                        <option value="">{{jtext('JGLOBAL_SELECT_AN_OPTION')}}</option>
                        {% for cal in calendars%}
                            <option value="{{cal.id}}">{{cal.name}}</option>
                        {%endfor%}
                    </select>
                </div>
            </div>


        </div>
    </div>

    <div class="row-fluid">
        <div class="span12">
            <div class="pull-right">

                <button class="btn btn-success" id="buttonSaveEvent">{{jtext('JSAVE')}}</button>
            </div>
        </div>
    </div>
</form>
<?php

namespace ITHilbert\Module\Classes;

class ViewCreater{

    private string $model;
    private array $types;

    public function __construct(string $modelName)
    {
        $this->types = Feldart::typenFuerTabelle((new $modelName)->getTable());
        $this->model = strtolower(class_basename($modelName));
    }

    public function getForm(){
        $content = '@extends("layouts.card")' . "\n\n";
        $content .= '@section("card-header", "'. ucfirst($this->model).'")' . "\n\n";

        //Form öffnen
        $content .= '@section("card-start")' . "\n";
        $content .= "\t<form method=\"POST\" action=\"{{ route('". $this->model .".store') }}\">\n";
        $content .= "\t\t@csrf\n";
        $content .= '@stop' . "\n\n";

        $content .= '@section("card-body")' . "\n";

        //Messages
        $content .= "\t@include('include.message')" . "\n\n";


        foreach ($this->types as $name => $type) {
            $label = str_replace('_',' ', ucfirst($name));
            if (str_ends_with($label, ' id')) {
                $label = str_replace(' id', '', $label);
            }

            if ($name == 'id') continue;
            if ($name == 'created_at') continue;
            if ($name == 'updated_at') continue;
            if ($name == 'deleted_at') continue;

            //Kommentar
            $content .= "\t{{-- ".$name." --}}\n";

            //Row
            $content .= "\t<div class=\"".config('module.row_classes')."\">\n";

            //Col Label
            $content .= "\t\t<div class=\"".config('module.col_label_classes')."\">\n";

            //Label
            if(config('module.label') != ''){
                $content .= "\t\t\t<label for=\"{$name}\" class=\"".config('module.label')."\">{$label}</label>\n";
            }else{
                $content .= "\t\t\t<label for=\"{$name}\">{$label}</label>\n";
            }
            //Col Label Ende
            $content .= "\t\t</div>\n";

            //Col Input
            $content .= "\t\t<div class=\"".config('module.col_input_classes')."\">\n";

            //Input Felder
            if (str_ends_with($name, '_id')) {
                $content .= $this->getComboBox($name, $type) . "\n";
            } else {
                $content .= "\t\t\t". $this->getInput($name, $type);
            }
            // Col Input Ende
            $content .= "\t\t</div>\n";
            //Row Ende
            $content .= "\t</div>\n";

        }
        $content .= '@stop' . "\n\n";

        //Form schließen
        $content .= '@section("card-ende")' . "\n";
        $content .= "\t</form>\n";
        $content .= '@stop' . "\n\n";


        //Buttons
        $content .= '@section("card-footer")' . "\n";
        $content .= "<row>\n";
        $content .= "\t<col-6>\n";
        $content .= "\t\t<button-back onclick=\"window.history.back();\">Zurück</button-back>\n";
        $content .= "\t</col-6>\n";
        $content .= "\t<col-6 class=\"d-flex justify-content-end\">\n";
        $content .= "\t\t<button-save>Speichern</button-save>\n";
        $content .= "\t</col-6>\n";
        $content .= "</row>\n";
        $content .= '@stop' . "\n\n";

        return $content;
    }

    public function getComboBox(string $name, string $type){
        return <<<HTML
                    <select name="{$name}" id="{$name}" @error('{$name}') class="is-invalid" @enderror>
                        @foreach(config('{$this->model}.{$name}', []) as \$row)
                            @if(\$row->id !== \${$this->model}->{$name})
                                <option value="{{\$row->id}}">{{\$row->name}}</option>
                            @else
                                <option value="{{\$row->id}}" selected>{{\$row->name}}</option>
                            @endif
                        @endforeach
                    </select>
        HTML;
    }

    public function getInput(string $name, string $type){
        return match (Feldart::fuerTyp($type)) {
            Feldart::Id => $this->getInputId($name),
            Feldart::Zahl => $this->getInputNumber($name),
            Feldart::Datum => $this->getInputDate($name),
            Feldart::Datumzeit => $this->getInputDatetime($name),
            Feldart::Boolean => $this->getInputBoolean($name),
            Feldart::Euro => $this->getInputEuro($name),
            Feldart::Text => $this->getInputText($name),
        };
    }

    public function getInputId(string $name){
        return '<intput-int name="'.$name.'" value="{{ old(\''.$name.'\', $'.$this->model.'->'.$name.') }}" @error(\''.$name.'\') class="is-invalid" @enderror /></input-int>'."\n";
    }

    public function getInputBoolean(string $name){
        return '<checkbox name="'.$name.'" value="{{ old(\''.$name.'\', $'.$this->model.'->'.$name.') }}" @error(\''.$name.'\') class="is-invalid" @enderror /></checkbox>'."\n";
    }

    public function getInputDate(string $name){
        return '<input-date name="'.$name.'" value="{{ old(\''.$name.'\', $'.$this->model.'->'.$name.') }}" @error(\''.$name.'\') class="is-invalid" @enderror /></input-date>'."\n";
    }

    public function getInputDatetime(string $name){
        return '<input-date name="'.$name.'" value="{{ old(\''.$name.'\', $'.$this->model.'->'.$name.') }}" @error(\''.$name.'\') class="is-invalid" @enderror /></input-date>'."\n";
    }


    public function getInputNumber(string $name){
        return '<input-number name="'.$name.'" value="{{ old(\''.$name.'\', $'.$this->model.'->'.$name.') }}" @error(\''.$name.'\') class="is-invalid" @enderror /></input-number>'."\n";
    }

    public function getInputEuro(string $name){
        return '<input-euro name="'.$name.'" value="{{ old(\''.$name.'\', $'.$this->model.'->'.$name.') }}" @error(\''.$name.'\') class="is-invalid" @enderror /></input-euro>'."\n";
    }

    public function getInputText(string $name){
        return '<input-text name="'.$name.'" value="{{ old(\''.$name.'\', $'.$this->model.'->'.$name.') }}" @error(\''.$name.'\') class="is-invalid" @enderror /></input-text>'."\n";
    }
}

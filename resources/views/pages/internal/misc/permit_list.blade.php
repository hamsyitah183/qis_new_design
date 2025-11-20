@extends('pages.app')

@section('pageName', 'Permit Condition List')


@section('breadcrumb')
    <x-breadcrumb 
        :items="[
            ['label' => 'Home', 'url' => '#'],
          
        ]" 
        title="Permit Condition List"
    >
     
    </x-breadcrumb>
@endsection

@section('content')

               <div class="row">
                        <div class="col-xl-12">
                            <div class="card custom-card">
                                <div class="card-header">
                                    <div class="card-title">
                                        TAGIFY JS
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row gy-3">
                                        <div class="col-xl-6">
                                            <label class="form-label d-block">Basic Tagify</label>
                                            <tags class="tagify form-control" tabindex="-1">
            <tag title="tag1" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="tag1"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">tag1</span></div></tag><span contenteditable="" tabindex="0" data-placeholder="​" aria-placeholder="" class="tagify__input" role="textbox" aria-autocomplete="both" aria-multiline="false"></span>
                ​
        </tags><input name="basic" value="tag1, tag2" autofocus="" class="form-control" tabindex="-1">
                                        </div>
                                        <div class="col-xl-6">
                                            <label class="form-label d-block">Tagify With Custom Suggestions</label>
                                            <tags class="tagify form-control some_class_name" tabindex="-1" aria-expanded="false">
            <tag title="css" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="css"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">css</span></div></tag><tag title="html" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="html"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">html</span></div></tag><tag title="javascript" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="javascript"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">javascript</span></div></tag><tag title="ActionScript" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag " value="ActionScript"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">ActionScript</span></div></tag><tag title="Adenine" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag " value="Adenine"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">Adenine</span></div></tag><tag title="A-0 System" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag " value="A-0 System"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">A-0 System</span></div></tag><tag title="A# (Axiom)" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag " value="A# (Axiom)"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">A# (Axiom)</span></div></tag><tag title="A++" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag " value="A++"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">A++</span></div></tag><span contenteditable="" tabindex="0" data-placeholder="write some tags" aria-placeholder="write some tags" class="tagify__input" role="textbox" aria-autocomplete="both" aria-multiline="false"></span>
                ​
        </tags><input name="input-custom-dropdown" class="form-control some_class_name" placeholder="write some tags" value="css, html, javascript" tabindex="-1">
                                        </div>
                                        <div class="col-xl-6">
                                            <label class="form-label d-block">Diasbled User Input</label>
                                            <tags class="tagify form-control" tabindex="-1" aria-expanded="false">
            <tag title="4" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag " value="4"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">4</span></div></tag><span tabindex="0" data-placeholder="Select tags from the list" aria-placeholder="Select tags from the list" class="tagify__input" role="textbox" aria-autocomplete="both" aria-multiline="false"></span>
                ​
        </tags><input name="tags-disabled-user-input" placeholder="Select tags from the list" class="form-control" tabindex="-1">
                                        </div>
                                        <div class="col-xl-6">
                                            <label class="form-label d-block">Drag &amp; Sort</label>
                                            <tags class="tagify  form-control" tabindex="-1"><tag title="tag 1" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="tag 1" draggable="true"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">tag 1</span></div></tag><tag title="tag 2" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="tag 2" draggable="true"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">tag 2</span></div></tag><tag title="tag 3" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="tag 3" draggable="true"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">tag 3</span></div></tag><tag title="tag 4" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="tag 4" draggable="true"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">tag 4</span></div></tag><tag title="tag 5" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="tag 5" draggable="true"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">tag 5</span></div></tag><tag title="tag 6" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="tag 6" draggable="true"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">tag 6</span></div></tag><span contenteditable="" tabindex="0" data-placeholder="​" aria-placeholder="" class="tagify__input" role="textbox" aria-autocomplete="both" aria-multiline="false"></span></tags><input name="drag-sort" class="form-control" value="tag 1, tag 2, tag 3, tag 4, tag 5, tag 6" tabindex="-1">
                                        </div>
                                        <div class="col-xl-6">
                                            <label class="form-label d-block">Tagify With Users List</label>
                                            <tags class="tagify form-control" tabindex="-1" aria-expanded="false">
            <tag title="abatisse2@nih.gov" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="3" name="Ardeen Batisse" avatar="https://i.pravatar.cc/80?img=3" email="abatisse2@nih.gov" team="A"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><div class="tagify__tag__avatar-wrap"><img onerror="this.style.visibility='hidden'" src="https://i.pravatar.cc/80?img=3"></div><span class="tagify__tag-text">Ardeen Batisse</span></div></tag><tag title="jhattersley0@ucsd.edu" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="1" name="Justinian Hattersley" avatar="https://i.pravatar.cc/80?img=1" email="jhattersley0@ucsd.edu" team="A"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><div class="tagify__tag__avatar-wrap"><img onerror="this.style.visibility='hidden'" src="https://i.pravatar.cc/80?img=1"></div><span class="tagify__tag-text">Justinian Hattersley</span></div></tag><tag title="mmandrake8@sourceforge.net" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag " value="9" name="Marvin Mandrake" avatar="https://i.pravatar.cc/80?img=9" email="mmandrake8@sourceforge.net" team="B"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><div class="tagify__tag__avatar-wrap"><img onerror="this.style.visibility='hidden'" src="https://i.pravatar.cc/80?img=9"></div><span class="tagify__tag-text">Marvin Mandrake</span></div></tag><tag title="foo@bar.com" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag " value="11" name="foo" avatar="https://i.pravatar.cc/80?img=11" email="foo@bar.com" team="B"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><div class="tagify__tag__avatar-wrap"><img onerror="this.style.visibility='hidden'" src="https://i.pravatar.cc/80?img=11"></div><span class="tagify__tag-text">foo</span></div></tag><span contenteditable="" tabindex="0" data-placeholder="​" aria-placeholder="" class="tagify__input" role="textbox" aria-autocomplete="both" aria-multiline="false"></span>
                ​
        </tags><input name="users-list-tags" value="abatisse2@nih.gov, Justinian Hattersley" class="form-control" tabindex="-1">
                                        </div>
                                        <div class="col-xl-6">
                                            <label class="form-label d-block">Tagify Single-Value Select</label>
                                            <tags class="tagify tagify--select selectMode form-control" spellcheck="false" tabindex="-1" title="" aria-expanded="false">
            <tag title="second option" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag " value="second option"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">second option</span></div></tag><span contenteditable="true" tabindex="0" data-placeholder="Please select" aria-placeholder="Please select" class="tagify__input" role="textbox" aria-autocomplete="both" aria-multiline="false"></span>
                ​
        </tags><input name="tags-select-mode" class="selectMode form-control" placeholder="Please select" tabindex="-1">
                                        </div>
                                        <div class="col-xl-6">
                                            <label class="form-label d-block">Readonly Tags</label>
                                            <tags class="tagify form-control" readonly="" tabindex="-1">
            <tag title="tag1" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="tag1"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">tag1</span></div></tag><tag title="tag 2" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="tag 2"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">tag 2</span></div></tag><tag title="another tag" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="another tag"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">another tag</span></div></tag><span tabindex="0" data-placeholder="​" aria-placeholder="" class="tagify__input" role="textbox" aria-autocomplete="both" aria-multiline="false"></span>
                ​
        </tags><input name="tags4" class="form-control" readonly="" value="tag1, tag 2, another tag" tabindex="-1">
                                        </div>
                                        <div class="col-xl-6">
                                            <label class="form-label d-block">Readonly Mix</label>
                                            <tags class="tagify  readonlyMix form-control" tabindex="-1">
            <tag title="read-only tag" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="foo" readonly="true" aria-readonly="true"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">foo</span></div></tag><tag title="bar" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="bar"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">bar</span></div></tag><tag title="Another readonly tag" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="Locked tag" readonly="true" aria-readonly="true"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">Locked tag</span></div></tag><span contenteditable="" tabindex="0" data-placeholder="Type something" aria-placeholder="Type something" class="tagify__input" role="textbox" aria-autocomplete="both" aria-multiline="false"></span>
                ​
        </tags><input name="tags-readonly-mix" type="text" class="readonlyMix form-control" placeholder="Type something" value="[
                                                {
                                                    &quot;value&quot;    : &quot;foo&quot;,
                                                    &quot;readonly&quot; : true,
                                                    &quot;title&quot;    : &quot;read-only tag&quot;
                                                },
                                                {
                                                    &quot;value&quot;    : &quot;bar&quot;
                                                },
                                                {
                                                    &quot;value&quot;    : &quot;Locked tag&quot;,
                                                    &quot;readonly&quot; : true,
                                                    &quot;title&quot;    : &quot;Another readonly tag&quot;
                                                }
                                            ]" tabindex="-1">
                                        </div>
                                        <div class="col-xl-12">
                                            <label class="form-label d-block">Tagify With Mix Text &amp; Tags</label>
                                            <tags class="tagify tagify--mix form-control" tabindex="-1">
            <span contenteditable="" tabindex="0" data-placeholder="​" aria-placeholder="" class="tagify__input" role="textbox" aria-autocomplete="both" aria-multiline="true">​<tag title="Eric Cartman" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" id="200" value="cartman"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">cartman</span></div></tag> and  do not know <tag title="homer simpson" contenteditable="false" spellcheck="false" tabindex="-1" class="tagify__tag tagify--noAnim" value="homer simpson" readonly="true"><x title="" class="tagify__tag__removeBtn" role="button" aria-label="remove tag"></x><div><span class="tagify__tag-text">homer simpson</span></div></tag> because he's a relic.<br></span>
                ​
        </tags><textarea name="mix" class="form-control" tabindex="-1">[[{"id":200, "value":"cartman", "title":"Eric Cartman"}]] and [[kyle]] do not know [[{"value":"homer simpson", "readonly":true}]] because he's a relic.</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

@endsection

@push('scripts')
<script>
    window.baseUrl = "{{ url('/') }}";
</script>

    @vite(['resources/js/pages/internal/misc/.js'])
@endpush


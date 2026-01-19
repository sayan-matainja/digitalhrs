@php use App\Helpers\AppHelper; @endphp
@php use App\Models\TeamMeeting; @endphp
<style>
    .img-wrap {
        position: relative;
        display: inline-block;
        font-size: 0;
    }

    .img-wrap .close {
        position: absolute;
        top: 2px;
        right: 2px;
        z-index: 100;
        background-color: #FFF;
        padding: 5px 2px 2px;
        color: #000;
        font-weight: bold;
        cursor: pointer;
        opacity: .5;
        text-align: center;
        font-size: 30px;
        line-height: 20px;
        border-radius: 50%;
    }

    .img-wrap:hover .close {
        opacity: 1;
    }
</style>

<div class="row">
    @if(!isset(auth()->user()->branch_id))
        <div class="col-lg-4 col-md-6 mb-4">
            <label for="branch_id" class="form-label">{{ __('index.branch') }} <span style="color: red">*</span></label>
            <select class="form-select" id="branch_id" name="branch_id">
                <option selected disabled>{{ __('index.select_branch') }}
                </option>
                @if(isset($companyDetail))
                    @foreach($companyDetail->branches()->get() as $key => $branch)
                        <option value="{{$branch->id}}"
                            {{ (isset($teamMeetingDetail) && ($teamMeetingDetail->branch_id ) == $branch->id) ? 'selected': '' }}>
                            {{ucfirst($branch->name)}}</option>
                    @endforeach
                @endif
            </select>
        </div>
    @endif
    <div class="col-lg-4 col-md-6 mb-4 internalTrainer">
        <label for="department_id" class="form-label">{{ __('index.department') }} <span
                style="color: red">*</span></label>
        <select class="form-select" id="department_id" multiple name="department[][department_id]">
            @if(isset($trainingDetail))
                @foreach($filteredDepartment as $department)
                    <option
                        value="{{ $department->id }}" {{ in_array($department->id, $departmentIds) ? 'selected' : '' }}>
                        {{ ucfirst($department->dept_name) }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <label for="title" class="form-label"> {{ __('index.meeting_title') }} <span style="color: red">*</span></label>
        <input type="text" class="form-control" id="title" name="title" required
               value="{{ ( isset( $teamMeetingDetail) ?  $teamMeetingDetail->title: old('title') )}}"
               autocomplete="off" placeholder="{{ __('index.enter_content_title') }}">
    </div>

    <div class="col-lg-4  mb-4 mb-3">
        <label for="venue" class="form-label">{{ __('index.meeting_venue') }} <span style="color: red">*</span> </label>
        <input type="text" class="form-control" id="venue" name="venue" required
               value="{{ ( isset( $teamMeetingDetail) ?  $teamMeetingDetail->venue: old('venue') )}}"
               autocomplete="off" placeholder="{{ __('index.enter_venue_name') }}">
    </div>

    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-6">
                <div class="meeting_date mb-4">
                    <label for="meeting_date" class="form-label">{{ __('index.meeting_date') }} <span
                            style="color: red">*</span></label>

                    @if(AppHelper::ifDateInBsEnabled())
                        <input class="form-control meetingDate"
                               name="meeting_date"
                               value="{{(isset($teamMeetingDetail) ? $teamMeetingDetail->meeting_date: old('meeting_date'))}}"
                               required
                               autocomplete="off"
                               type="text"
                               id="meetingDate"
                               placeholder="yyyy/mm/dd"
                        />
                    @else
                        <input class="form-control"
                               name="meeting_date"
                               value="{{(isset($teamMeetingDetail) ? $teamMeetingDetail->meeting_date: old('meeting_date'))}}"
                               required
                               autocomplete="off"
                               type="date"
                        />
                    @endif
                </div>
                <div class="meeting_time mb-4">
                    <label for="meeting_start_time" class="form-label">{{ __('index.meeting_start_time') }} <span
                            style="color: red">*</span> </label>
                    <input type="time" class="form-control" id="meeting_start_time" name="meeting_start_time" required
                           value="{{ ( isset( $teamMeetingDetail) ?  $teamMeetingDetail->meeting_start_time: old('meeting_start_time') )}}"
                           autocomplete="off">
                </div>
                <div class="meeting_participator mb-4">
                    <label for="team_meeting" class="form-label">{{ __('index.meeting_participator') }} <span
                            style="color: red">*</span></label>
                    <br>
                    <select class="form-select" id="team_meeting" name="participator[][meeting_participator_id]"
                            multiple="multiple" required>

                    </select>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="meeting_description mb-4">
                    <label for="description" class="form-label">{{ __('index.meeting_description') }} <span
                            style="color: red">*</span></label>
                    <textarea class="form-control" minlength="10" name="description" id=""
                              rows="6">{!! ( isset( $teamMeetingDetail) ?  $teamMeetingDetail->description: old('description') ) !!} </textarea>
                </div>

                <div class="meeting_upload mb-4">
                    <label for="image" class="form-label">{{ __('index.upload_image') }}</label>
                    <input class="form-control" type="file" accept="image/png, image/jpeg,image/jpg, image/svg,"
                           id="image" name="image"/>
                    <small>*{{ __('index.image_hint') }}</small>

                    @if(isset($teamMeetingDetail) && $teamMeetingDetail->image)
                        <div class="img-wrap mt-3" style="object-fit: contain">
                            <span class="close removeImage"
                                  data-href="{{route('admin.team-meetings.remove-image',$teamMeetingDetail->id)}}">&times;</span>
                            <img src="{{asset(TeamMeeting::UPLOAD_PATH.$teamMeetingDetail->image)}}"
                                 alt="" width="200"
                                 height="200">
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>


    <input type="hidden" readonly id="teamNotification" name="notification" value="0">


    <div class="mb-2 text-center text-md-start">
        <button type="submit"
                class="btn btn-primary mb-2">{{isset($teamMeetingDetail) ? __('index.update') : __('index.create')}} </button>
        <button type="submit" id="withTeamNotification" class="btn btn-primary mb-2">
            <i class="link-icon" data-feather="plus"></i>
            {{isset($teamMeetingDetail)?  __('index.update_send'): __('index.create_send')}}
        </button>
    </div>

</div>








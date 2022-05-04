// class Schedule extends PureComponent {
//     constructor(props) {
//         super(props);
//
//         this.state = {
//             'modalOptions': this.handleModalOptions(props.modal),
//             'isModal': (props && 'modal' in props) ? true : false,
//             'clinics': [],
//             'filter': {status: "unscheduled"},
//             'schedule': [],
//             'selectedSchedule': {},
//             "isUnscheduled": true,
//             "filterSetting": {
//                 "patientID": "text",
//                 "reqDate": "date"
//             },
//             "reqDateShift": {},
//             "allClinics": {},
//             "chosenClinicID": "",
//             "status": {}
//         };
//
//         this.vclinicModel = new VclinicModel();
//         this.schedultModel = new ScheduleModel();
//
//         Lang.load('teleclinic', 'teleclinic').then(r => {
//             this.setPureState({reqDateShift: {
//                     "1": Lang.t("schedule.reqDateShift.morning"),
//                     "2": Lang.t("schedule.reqDateShift.afternoon"),
//                 }, status: {
//                     "": "",
//                     "scheduled": Lang.t("schedule.status.scheduled"),
//                     "canceled": Lang.t("schedule.status.canceled"),
//                     "completed": Lang.t("schedule.status.completed")
//                 }});
//         });
//
//     }
//
//     isModal() {
//         return this.state && this.state.isModal;
//     }
//
//     initPrescription() {
//         let bodyValue = "";
//         Array(10).fill(0).forEach(() => {
//            bodyValue += `
//            <tr style="min-height: 35px; height: 35px">
//                         <td style="word-wrap: break-word"></td>
//                         <td style="word-wrap: break-word"></td>
//                         <td style="word-wrap: break-word"></td>
//                         <td style="word-wrap: break-word"></td>
//                         <td style="word-wrap: break-word"></td>
//                     </tr>
//            `;
//         });
//         return (
//             `<table class="table table-bordered table-hover overflow-auto bg-white">
//                 <thead>
//                 <tr>
//                     <th scope="col" style="width: 5%">STT</th>
//                     <th scope="col" style="width: 40%">${Lang.t("schedule.prescription.drugName")}</th>
//                     <th scope="col" style="width: 35%">${Lang.t("schedule.prescription.dose")}</th>
//                     <th scope="col" style="width: 10%">${Lang.t("schedule.prescription.unit")}</th>
//                     <th scope="col" style="width: 10%">${Lang.t("schedule.prescription.quantity")}</th>
//                 </tr>
//                 </thead>
//                 <tbody>
//                     ${bodyValue}
//
//                 </tbody>
//
//
//             </table>`
//         );
//
//     }
//
//     handleModalOptions(opts) {
//         //default
//         return $.extend({
//             'multiple': false,
//             'type': 'department'
//         }, opts);
//     }
//
//     componentWillReceiveProps(nexProps) {
//         if (!nexProps)
//             return;
//         if ('modal' in nexProps) {
//             this.setState({ 'modalOptions': this.handleModalOptions(nexProps.modal) });
//         } else {
//             this.setState({ 'modalOptions': {} });
//         }
//     }
//
//     componentWillMount() {
//         if(!this.isModal()){
//             App.Component.trigger('leftNav.active', 'Schedule');
//         }
//     }
//
//     componentDidUpdate() {
//         this.trigger('update');
//     }
//
//     componentDidMount() {
//         App.requireLogin();
//         App.Component.trigger('leftNav.show', true);
//         this.getCLinics();
//         this.getSchedule();
//     }
//
//     getCLinics() {
//
//         return new Promise(done => {
//             this.vclinicModel.getClinics({siteID: App.siteID, groupBy: "depID"})
//                 .then(resp => {
//                     delete resp["version"];
//                     let allClinics = {};
//                     resp.forEach(department => {
//                         department.clinics.forEach(clinic => {
//                             allClinics[clinic.id] = clinic.name;
//                         })
//                     });
//
//                     let defaultClinicID = "";
//                     if (!jQuery.isEmptyObject(allClinics))
//                         defaultClinicID = Object.keys(allClinics)[0];
//
//                     this.setState({clinics: resp, allClinics: allClinics, chosenClinicID: defaultClinicID}, () => {done(resp)});
//                 })
//         });
//     }
//
//     getSchedule() {
//         return new Promise(done => {
//             this.schedultModel.getSchedule(this.state.filter)
//                 .then(resp => {
//                     delete resp["version"];
//                     this.setState({schedule: resp}, () => {done(resp)});
//                 })
//         });
//     }
//
//     changeActiveElement(ev) {
//         $( ".dep-title" ).each(function( index ) {
//             $(this).removeClass("dep-title-active");
//         });
//
//         $(ev.target).addClass("dep-title-active");
//     }
//
//     selectUnscheduled(ev) {
//         this.state.filter = {};
//         this.state.filter.status = "unscheduled";
//         this.changeActiveElement(ev);
//         this.setPureState({filter: this.state.filter, isUnscheduled: true, selectedSchedule: {}}, () => {this.getSchedule();});
//     }
//
//     selectClinic(ev, id) {
//         this.state.filter = {};
//         this.state.filter.clinicID = id;
//         this.changeActiveElement(ev);
//         this.setPureState({filter: this.state.filter, isUnscheduled: false, selectedSchedule: {}}, () => {this.getSchedule();});
//     }
//
//     selectSchedule(ev, item) {
//
//         $( ".schedule-table tr" ).each(function( index ) {
//             $(this).removeClass("schedule-active");
//         });
//
//         $(ev.target).closest("tr").addClass("schedule-active");
//         if (!item.diagPrescription)
//             item.diagPrescription = this.initPrescription();
//         this.setPureState({selectedSchedule: item});
//     }
//
//     chooseClinic(ev) {
//         this.setPureState({chosenClinicID: ev.target.value})
//     }
//
//     schedule(ev) {
//         ev.preventDefault();
//         let scheduleDate = $("#form-schedule-date").val();
//
//         if (!scheduleDate) {
//             $.toast({
//                 text : Lang.t("schedule.error.emptyDate"),
//                 position : 'top-right'
//             })
//             return;
//         }
//
//         this.schedultModel.
//             confirmSchedule(
//                 this.state.selectedSchedule.id,
//             {scheduledDate: scheduleDate, vclinicID: this.state.chosenClinicID})
//             .then(resp => {
//                 if (resp && resp["status"] === true) {
//                     this.getSchedule();
//                     this.setPureState({selectedSchedule: {}});
//                     $.toast({
//                         text : Lang.t("schedule.success"),
//                         position : 'top-right'
//                     })
//                 } else {
//                     console.log(resp);
//                     $.toast({
//                         text : Lang.t("schedule.error"),
//                         position : 'top-right'
//                     })
//                 }
//             });
//
//     }
//
//     cancelSchedule(ev) {
//         ev.preventDefault();
//         let cancelReason = $("#form-schedule-cancel").val();
//
//         if (!cancelReason) {
//             $.toast({
//                 text : Lang.t("schedule.error.emptyCancelReason"),
//                 position : 'top-right'
//             })
//             return;
//         }
//
//         this.schedultModel.
//         cancelSchedule(
//             this.state.selectedSchedule.id, {comment: cancelReason})
//             .then(resp => {
//                 if (resp && resp["status"] === true) {
//                     this.getSchedule();
//                     $.toast({
//                         text : Lang.t("schedule.success"),
//                         position : 'top-right'
//                     })
//                 } else {
//                     console.log(resp);
//                     $.toast({
//                         text : Lang.t("schedule.error"),
//                         position : 'top-right'
//                     })
//                 }
//             });
//
//     }
//
//     onChangeSelectedSchedule(ev, key) {
//         this.state.selectedSchedule[key] = ev.target.value;
//         this.setPureState({selectedSchedule: this.state.selectedSchedule});
//     }
//
//     handleChangePrescription = function (ev){
//         this.onChangeSelectedSchedule(ev, "diagPrescription");
//     }.bind(this);
//
//     diagnosis(ev) {
//         ev.preventDefault();
//
//         this.schedultModel.
//             diagnosis(
//                 this.state.selectedSchedule.id,
//                 {
//                     "diagDesc": this.state.selectedSchedule["diagDesc"],
//                     "diagConclusion": this.state.selectedSchedule["diagConclusion"],
//                     "diagRecommendation": this.state.selectedSchedule["diagRecommendation"],
//                     "diagPrescription": this.state.selectedSchedule["diagPrescription"],
//                     "reExamDate": this.state.selectedSchedule["reExamDate"]
//                 })
//                 .then(resp => {
//                     if (resp && resp["status"] === true) {
//                         $( ".schedule-active" ).each(function( index ) {
//                             $(this).removeClass("schedule-active");
//                         });
//                         this.setState({selectedSchedule: {}});
//                         $.toast({
//                             text : Lang.t("schedule.success"),
//                             position : 'top-right'
//                         })
//                     } else {
//                         console.log(resp);
//                         $.toast({
//                             text : Lang.t("schedule.error"),
//                             position : 'top-right'
//                         })
//                     }
//                 });
//     }
//
//     search(key, value) {
//         if (!value)
//             delete this.state.filter[key];
//         else
//             this.state.filter[key] = value;
//         this.setState({filter: this.state.filter}, () => {
//             this.getSchedule();
//         });
//     }
//
//     pageContent() {
//         return (
//             <div className="main">
//                 <div className="row" style={{height: '100%'}}>
//                     <div className="col-sm-2 border border-dark h-100 overflow-x-auto pl-0 pr-0 break-line theme-light">
//                         <ul className="schedule-nav">
//                             <li onClick={(ev) => {this.selectUnscheduled(ev)}}><a className="dep-title dep-title-active">{Lang.t('schedule.status.unscheduled')}</a></li>
//                             {
//                                 this.state.clinics.map(department =>
//
//                                     <React.Fragment key={department.id}>
//                                         <li><a>{department.name}</a></li>
//                                         <ul className="child-nav">
//                                             {department.clinics.map(clinic =>
//                                                 <li className="d-block" onClick={(ev) => {this.selectClinic(ev, clinic.id);}} key={clinic.id}><a className="dep-title pl-5">{clinic.name}</a></li>
//                                             )}
//                                         </ul>
//                                     </React.Fragment>
//
//                                 )
//                             }
//                         </ul>
//                     </div>
//
//                     <div className="col-sm-10 border border-dark h-100 pl-0 pr-0 break-line theme-light" >
//                         <div>
//                             <form>
//                                 <div className="row p-3" >
//                                     <div className="col" style={{maxWidth: "250px"}}>
//                                         <label htmlFor="searchPatientName">{Lang.t("schedule.patientName")}</label>
//                                         <input id="searchPatientName" type="text" className="form-control"
//                                                onChange={(ev) => {this.search("patientName", ev.target.value)}}/>
//                                     </div>
//                                     {this.state.isUnscheduled &&
//                                     <div className="col" style={{maxWidth: "250px"}}>
//                                         <label htmlFor="searchReqDate">{Lang.t("schedule.reqDate")}</label>
//                                         <input id="searchReqDate" type="date" className="form-control"
//                                                onChange={(ev) => {this.search("reqDate", ev.target.value)}}/>
//                                     </div>
//                                     }
//                                     {!this.state.isUnscheduled &&
//                                         <React.Fragment>
//                                             <div className="col" style={{maxWidth: "250px"}}>
//                                                 <label htmlFor="searchScheduleStatus">{Lang.t("schedule.status")}</label>
//                                                 <select className="form-control input-group" id="searchScheduleStatus"
//                                                         onChange={(ev) =>{this.search("status", ev.target.value)}}
//                                                         style={{maxWidth: "250px"}}>
//                                                     {
//                                                         Object.keys(this.state.status).map((key) =>
//                                                             <option key={key} value={key} >{this.state.status[key]}</option>
//                                                         )
//                                                     }
//
//                                                 </select>
//                                             </div>
//                                             <div className="col" style={{maxWidth: "250px"}}>
//                                                 <label htmlFor="searchScheduleDate">{Lang.t("schedule.date")}</label>
//                                                 <input id="searchScheduleDate" type="date" className="form-control"
//                                                        onChange={(ev) => {this.search("scheduledDate", ev.target.value)}}/>
//                                             </div>
//                                         </React.Fragment>
//
//                                     }
//                                 </div>
//                             </form>
//                         </div>
//                         <div className="row m-0" style={{height: "calc(100vh - 154px)"}}>
//                             <div className="col-sm-4 border border-dark h-100 overflow-auto pl-0">
//                                 <table className="table table-hover schedule-table pl-0">
//                                     <thead>
//                                     <tr>
//                                         <th scope="col" style={{minWidth: "40px"}}>STT</th>
//                                         <th scope="col" style={{minWidth: "250px"}}>{Lang.t("schedule.patientName")}</th>
//                                         <th scope="col" style={{minWidth: "40px"}}>{Lang.t("schedule.patientAge")}</th>
//                                         <th scope="col" style={{minWidth: "40px"}}>{Lang.t("schedule.patientSex")}</th>
//                                         {this.state.isUnscheduled &&
//                                         <React.Fragment>
//                                             <th style={{minWidth: "120px"}} scope="col">{Lang.t("schedule.reqDate")}</th>
//                                             <th style={{minWidth: "100px"}} scope="col">{Lang.t("schedule.reqDateShift")}</th>
//                                             {/*<th scope="col">{Lang.t("schedule.note")}</th>*/}
//                                             <th style={{minWidth: "200px"}} scope="col">{Lang.t("schedule.paymentStatus")}</th>
//                                         </React.Fragment>
//                                         }
//                                         {!this.state.isUnscheduled &&
//                                         <React.Fragment>
//                                             <th style={{minWidth: "250px"}} scope="col">{Lang.t("schedule.date")}</th>
//                                             <th style={{minWidth: "250px"}} scope="col">{Lang.t("schedule.doctorName")}</th>
//                                             <th scope="col" style={{minWidth: "150px"}}>{Lang.t("schedule.diagDate")}</th>
//                                             <th scope="col" style={{minWidth: "120px"}}>{Lang.t("schedule.status")}</th>
//                                         </React.Fragment>
//                                         }
//
//                                     </tr>
//                                     </thead>
//                                     <tbody>
//                                     {
//                                         this.state.schedule.map((item, index) =>
//                                             <tr key={index} onClick={(ev) => {this.selectSchedule(ev, item)}}>
//                                                 <th scope="row">{index+1}</th>
//                                                 <td>{item["patient"]["name"]}</td>
//                                                 <td>{item["patient"]["age"]}</td>
//                                                 <td>{item["patient"]["sex"]}</td>
//                                                 {this.state.isUnscheduled &&
//                                                 <React.Fragment>
//                                                     <td>{item["reqDate"]}</td>
//                                                     <td>{this.state.reqDateShift[item["reqDateShift"]]}</td>
//                                                     {/*<td>{item["reqNote"]}</td>*/}
//                                                     <td>{Lang.t("schedule.paymentStatus." + item["paymentStatus"])}</td>
//                                                 </React.Fragment>
//                                                 }
//                                                 {!this.state.isUnscheduled &&
//                                                 <React.Fragment>
//                                                     <td>{item["scheduledDate"]}</td>
//                                                     <td>Doctor 1</td>
//                                                     <td>{item["diagDate"]}</td>
//                                                     <td>{Lang.t("schedule.status." + item["status"])}</td>
//                                                 </React.Fragment>
//                                                 }
//
//                                             </tr>
//                                         )
//                                     }
//
//                                     </tbody>
//                                 </table>
//                             </div>
//                             <div className="col-sm-8 border border-dark h-100 p-0 m-0">
//                                 {
//                                     !jQuery.isEmptyObject(this.state.selectedSchedule) &&
//                                     <div className="h-100">
//
//                                         <div className="detail-information">
//                                             <div className="mb-2">
//                                                 <span>{Lang.t("schedule.patientName")}: {this.state.selectedSchedule["patient"]["name"]}</span>
//                                                 <span>{Lang.t("schedule.patientAge")}: {this.state.selectedSchedule["patient"]["age"]}</span>
//                                                 <span>{Lang.t("schedule.patientSex")}: {this.state.selectedSchedule["patient"]["sex"]}</span>
//                                             </div>
//                                             <p>{Lang.t("schedule.patientAddress")}: {this.state.selectedSchedule["patient"]["address"]}</p>
//
//                                             <div className="mb-2">
//                                                 <span>{Lang.t("schedule.patientPhone")}: {this.state.selectedSchedule["patient"]["phone"]}</span>
//                                                 <span>{Lang.t("schedule.reqDateShift")}: {this.state.reqDateShift[this.state.selectedSchedule["reqDateShift"]]}</span>
//                                             </div>
//                                             <p>{Lang.t("schedule.note")}: {this.state.selectedSchedule["reqNote"]}</p>
//                                             <p>{Lang.t("schedule.paymentStatus")}: {Lang.t("schedule.paymentStatus." + this.state.selectedSchedule["paymentStatus"])}</p>
//
//                                             {
//                                                 !this.state.isUnscheduled &&
//                                                 <React.Fragment>
//                                                     <p>{Lang.t("schedule.date")}: {this.state.selectedSchedule["scheduledDate"]}</p>
//                                                 </React.Fragment>
//                                             }
//
//                                         </div>
//
//
//                                         {/*form cho truong hop da sap lich*/}
//                                         {
//                                             !this.state.isUnscheduled &&
//                                                 <React.Fragment>
//                                                     <button type="submit" className="btn btn-primary ml-3" onClick={(ev) => {this.diagnosis(ev)}}>{Lang.t("teleclinic.btnConfirmDiagnosis")}</button>
//                                                     <form className="pl overflow-auto" style={{height: "calc(100vh - 382px)"}}>
//
//                                                         <div className="form-group pl-3">
//                                                             <label htmlFor="diagDesc">{Lang.t("schedule.diagDesc")}</label>
//                                                             <textarea className="form-control" id="diagDesc"
//                                                                       rows="5"
//                                                                       defaultValue={this.state.selectedSchedule["diagDesc"]}
//                                                                       onChange={(ev) => {this.onChangeSelectedSchedule(ev, "diagDesc")}}
//                                                             />
//                                                         </div>
//                                                         <div className="form-group pl-3">
//                                                             <label htmlFor="diagConclusion">{Lang.t("schedule.diagConclusion")}</label>
//                                                             <textarea className="form-control" id="diagConclusion"
//                                                                       rows="3"
//                                                                       defaultValue={this.state.selectedSchedule["diagConclusion"]}
//                                                                       onChange={(ev) => {this.onChangeSelectedSchedule(ev, "diagConclusion")}}/>
//                                                         </div>
//                                                         <div className="form-group pl-3">
//                                                             <label htmlFor="diagRecommendation">{Lang.t("schedule.diagRecommendation")}</label>
//                                                             <textarea className="form-control" id="diagRecommendation"
//                                                                       rows="3"
//                                                                       defaultValue={this.state.selectedSchedule["diagRecommendation"]}
//                                                                       onChange={(ev) => {this.onChangeSelectedSchedule(ev, "diagRecommendation")}}/>
//                                                         </div>
//                                                         <div className="form-group pl-3">
//                                                             <label htmlFor="searchReExamDate">{Lang.t("schedule.reExamDate")}</label>
//                                                             <input id="searchReExamDate" type="text" className="form-control"
//                                                                    value={this.state.selectedSchedule["reExamDate"] ? this.state.selectedSchedule["reExamDate"]: ""}
//                                                                    onChange={(ev) => {this.onChangeSelectedSchedule(ev, "reExamDate")}}/>
//                                                         </div>
//                                                         <div className="form-group pl-3">
//                                                             <label htmlFor="diagPrescription">{Lang.t("schedule.diagPrescription")}</label>
//                                                             {/*<textarea className="form-control" id="diagPrescription"*/}
//                                                             {/*          rows="10" value={this.state.selectedSchedule["diagPrescription"]}/>*/}
//                                                         </div>
//                                                         <ContentEditable html={this.state.selectedSchedule["diagPrescription"]}
//                                                                          onChange={this.handleChangePrescription}
//                                                         />
//                                                     </form>
//                                                 </React.Fragment>
//
//                                         }
//
//                                         {/*form cho truong hop chua sap lich*/}
//                                         {
//                                             this.state.isUnscheduled &&
//                                             <form className="pl-3">
//                                                 <button onClick={(ev) => {this.schedule(ev)}} type="submit" className="btn btn-primary">{Lang.t("teleclinic.btnConfirmSchedule")}</button>
//                                                 {/*<button onClick={(ev) => this.cancelSchedule(ev)} type="submit" className="btn btn-danger">{Lang.t("teleclinic.btnCancelSchedule")}</button>*/}
//
//                                                 {/*<div className="form-group row">*/}
//                                                 {/*    <div className="col-sm-3">*/}
//                                                 {/*        <label htmlFor="form-schedule-date">{Lang.t("schedule.cancelReason")}</label>*/}
//                                                 {/*    </div>*/}
//                                                 {/*    <div className="col-sm-5">*/}
//                                                 {/*        <input type="text" id="form-schedule-cancel" style={{width: "250px"}}/>*/}
//                                                 {/*    </div>*/}
//
//                                                 {/*</div>*/}
//
//                                                 <div className="form-group row">
//                                                     <div className="col-sm-3">
//                                                         <label htmlFor="form-schedule-date">{Lang.t("schedule.date")}</label>
//                                                     </div>
//                                                     <div className="col-sm-5">
//                                                         <input type="datetime-local" id="form-schedule-date" style={{width: "250px"}}/>
//                                                     </div>
//
//                                                 </div>
//                                                 <div className="form-group row">
//                                                     <div className="col-sm-3">
//                                                         <label htmlFor="sel-chooseClinic">{Lang.t("schedule.chooseClinic")}</label>
//                                                     </div>
//                                                     <div className="col-sm-5">
//                                                         <select className="form-control input-group" id="sel-chooseClinic"
//                                                                 value={this.state.chosenClinicID} onChange={(ev) => this.chooseClinic(ev)}
//                                                                 style={{width: "250px"}}
//                                                         >
//                                                             {
//                                                                 this.state.clinics.map(department =>
//                                                                     <optgroup key={department.id} label={department.name}>
//                                                                         {department.clinics.map(clinic =>
//                                                                             <option key={clinic.id} value={clinic.id} >{clinic.name}</option>
//                                                                         )}
//                                                                     </optgroup>
//                                                                 )
//                                                             }
//
//                                                         </select>
//                                                     </div>
//                                                 </div>
//
//                                             </form>
//                                         }
//
//                                     </div>
//                                 }
//
//                             </div>
//                         </div>
//
//                     </div>
//
//                 </div>
//             </div>
//         )
//     }
//
//     render() {
//         if (this.isModal())
//             return this.pageContent();
//         else
//             return (
//                 <AdminLayout>
//                     {this.pageContent()}
//                 </AdminLayout>
//             );
//     }
// }
//
// class ContentEditable extends PureComponent{
//
//     constructor(props) {
//         super(props);
//         this.ref = React.createRef();
//     }
//
//     render(){
//         return <div
//             style={{backgroundColor: "white"}}
//     ref={this.ref}
//     onInput={this.emitChange.bind(this)}
//     onBlur={this.emitChange.bind(this)}
//     contentEditable
//     dangerouslySetInnerHTML={{__html: this.props.html}}/>;
//     }
//
//     shouldComponentUpdate(nextProps){
//         return nextProps.html !== this.ref.current.innerHTML;
//     }
//
//     emitChange(){
//         let html = this.ref.current.innerHTML;
//         if (this.props.onChange && html !== this.lastHtml) {
//
//             this.props.onChange({
//                 target: {
//                     value: html
//                 }
//             });
//         }
//         this.lastHtml = html;
//     }
// }
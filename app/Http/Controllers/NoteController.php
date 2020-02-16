<?php

namespace App\Http\Controllers;

use App\Helpers\AcademicYear as Inscrip;
use App\Http\Requests\CreateNoteRequest;
use App\Models\AcademicYear;
use App\Models\Contrat;
use App\Repositories\ContratRepository;
use App\Repositories\CycleRepository;
use App\Repositories\EcueRepository;
use App\Repositories\EnseignementRepository;
use App\Repositories\NoteRepository;
use App\Repositories\ResultatNominatifRepository;
use App\Repositories\SemestreInfoRepository;
use App\Repositories\SemestreRepository;
use App\Repositories\SpecialiteRepository;
use App\Repositories\UeInfoRepository;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class NoteController extends Controller
{
    protected $specialiteRepository;
    protected $cycleRepository;
    protected $semestreRepository;
    protected $enseignementRepository;
    protected $anneeAcademic;
    protected $contratRepository;
    protected $noteRepository;
    protected $ecueRepository;
    protected $ueInfoRepository;
    protected $semestreInfoRepository;
    protected $resultatNominatifsRepository;

    public function __construct(CycleRepository $cycleRepository, SpecialiteRepository $specialiteRepository,
                                SemestreRepository $semestreRepository, EnseignementRepository $enseignementRepository,
                                ContratRepository $contratRepository, Inscrip $academicYear,
                                NoteRepository $noteRepository, EcueRepository $ecueRepository, UeInfoRepository $ueInfoRepository,
                                SemestreInfoRepository $semestreInfoRepository, ResultatNominatifRepository $resultatNominatifRepository)
    {
        $this->cycleRepository = $cycleRepository;
        $this->specialiteRepository = $specialiteRepository;
        $this->semestreRepository = $semestreRepository;
        $this->enseignementRepository = $enseignementRepository;
        $this->anneeAcademic = AcademicYear::find($academicYear->getCurrentAcademicYear());

        $this->contratRepository = $contratRepository;
        $this->noteRepository = $noteRepository;
        $this->ecueRepository = $ecueRepository;
        $this->semestreInfoRepository = $semestreInfoRepository;
        $this->ueInfoRepository = $ueInfoRepository;
        $this->resultatNominatifsRepository = $resultatNominatifRepository;
    }

    public function search($n, $type = null){
        $specialites = $this->specialiteRepository->all();
        $cycles = $this->cycleRepository->all();
        if($n == '2')
            $method = 'imprime';
        elseif($n == '1')
            $method = 'affiche';
        elseif($n == '3')
            $method = 'deliberation';
        elseif($n == '4')
            $method = 'rattrapage';
        elseif ($n == '5'){
            $method = 'pv';
        }
        elseif ($n == '6'){
            $method = 'pvcc';
        }
        $model = 'notes';

        return view('search',compact('cycles','model', 'method', 'type'));
    }

    /**
     * @param $sem for semester
     * @param $spe for speciality
     * cette fonction sert à l'enregistrement des notes de l'etudiant
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function affiche($sem, $spe){
        $semestre = $this->semestreRepository->findWithoutFail($sem);
        $specialite = $this->specialiteRepository->findWithoutFail($spe);
        $ecues = $specialite->ecues->where('semestre_id', $semestre->id);
        $aa = $this->anneeAcademic;
        $ens = [];
        foreach($ecues as $ec){
            $enseignement = $ec->enseignements->where('specialite_id', $specialite->id)->where('academic_year_id', '==', $aa->id)->first();
            isset($enseignement->id) ? array_push($ens, $enseignement->id) : '';
        }
        $enseignements = $this->enseignementRepository->findWhereIn('id', $ens);
//        dd($enseignements);

        return view('notes.affiche', compact('enseignements', 'specialite', 'semestre'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deliberation($sem, $spec){
        $specialite = $this->specialiteRepository->findWithoutFail($spec);
        $semestre = $this->semestreRepository->findWithoutFail($sem);
        $contrats = $this->contratRepository->findWhere([
            'specialite_id' => $specialite->id,
            'cycle_id' => $semestre->cycle->id,
            'academic_year_id' => $this->anneeAcademic->id
        ]);

        return view('notes.deliberation', compact('specialite', 'semestre', 'contrats'));

    }

    public function noteDeliberation($type, $app, $sem){
        $contrat = $this->contratRepository->findWithoutFail($app);
        $semestre = $this->semestreRepository->findWithoutFail($sem);
        $ecues = $contrat->specialite->ecues->where('semestre_id', $semestre->id); // toutes les ecues de la specialite de l'etudiant.

        $denied = false; //pour verifier que les notes de 1ere session ont ete deja renseignees

        $enseignements = []; //conteneur dans lequel seront chargés tous les enseignements concernés

        foreach($ecues as $ecue){
            $ens = $ecue->enseignements->where('specialite_id', $contrat->specialite_id)->where('academic_year_id', '==', $this->anneeAcademic->id)->first();
            ($ens) ? $enseignements[] = $ens : '';
        }

        /**
         * Pour chaque enseigements verifier que l'etudiant possede une note et
         * qu'il possede aussi une note dans la session dans laquelle il va etre delibere.
         */

        foreach ($enseignements as $e){
//            dd($type);
            if($contrat->notes->where('enseignement_id', $e->id)->first() && (($type == 'session1') ? $contrat->notes->where('enseignement_id', $e->id)->first()->session1 == null : $contrat->notes->where('enseignement_id', $e->id)->first()->session2 == null))
                $denied = true;
            elseif (!$contrat->notes->where('enseignement_id', $e->id)->first())
                $denied = true;
        }

        if($denied){
            Flash::error('Veuillez renseigner les notes de '.$type .' de tous les etudiants avant de deliberer');
            return redirect()->back();
        }

        return view('notes.noteDeliberation', compact('contrat', 'enseignements', 'type', 'sem'));
    }

    public function saveDeliberation($sem, $type, $contrat, Request $request){
        $input = $request->except('_token');
//        if($type == 'session2'){
//            dd($input);
//        }
        $contrat = $this->contratRepository->findWithoutFail($contrat);
        $semestre = $this->semestreRepository->findWithoutFail($sem);

        foreach ($input as $key => $value){
            $enseignement = $this->enseignementRepository->findWithoutFail($key);
            $note = $this->noteRepository->findWhere(
                ['enseignement_id' => $enseignement->id, 'contrat_id' => $contrat->id]
            )->first();

            if ($type = 'session1'){
                $note->update('del1', $value);
            }
            elseif ($type = 'session2'){
                $note->update('del2', $value);
            }
        }


        $enseignements = $semestre->enseignements->where('specialite_id', $contrat->specialite_id)->where('academic_year_id', $contrat->academic_year_id);

        $this->saveNotes($contrat, $enseignements, $type, $sem);

//        $ues = [];
//        foreach($enseignements as $ens){
//            $ues[$ens->ue_id] = $ens->ue;
//        }
//
//        $semestreInfo = $this->semestreInfoRepository->firstOrNew(['semestre_id'=>$sem, 'contrat_id' => $contrat->id]);
//        $semestreInfo->session = $type;
//        $elimSemestre = false;
//        $creditObtsem = 0;
//        $nbUeValid = 0;
//        $totalSem = 0;
//        foreach($ues as $ue) {
//            $ueInfo = $this->ueInfoRepository->firstOrNew(['ue_id' => $ue->id, 'contrat_id' => $contrat->id]);
//            $elim = false;
//            $creditTot = 0;
//            $creditObt = 0;
//            $totalUe = 0;
//
//            foreach ($input as $key => $value) {
//                $enseignement = $this->enseignementRepository->findWithoutFail($key);
//
//                if($enseignement->ue_id == $ue->id) {
//                    $creditTot += $enseignement->credits;
//                    if($value < 5){
//                        $elim=$elimSemestre=true;
//                    }
//                    if($value >=10)
//                        $creditObt += $enseignement->credits;
//
//                    $note = $this->noteRepository->findWhere(
//                        ['enseignement_id' => $enseignement->id, 'contrat_id' => $contrat->id]
//                    )->first();
//
//                    if ($type == 'session1') {
//
//                        $note->update(['del1' => $value]);
//                        $note->update(['del2' => $value]);
//                        $totalUe += $note->del1*$enseignement->credits;
//
//                    } elseif ($type == 'session2') {
//                        $note->update(['del2' => $value]);
//                        $totalUe += $note->del2*$enseignement->credits;
//                    }
//                }
//            }
//            $ueInfo->creditObt = $creditObt;
//            $ueInfo->creditTot = $creditTot;
//            $ueInfo->moyenne = $totalUe / $ueInfo->creditTot;
//            $ueInfo->totalNotes = $totalUe;
//
//            $totalSem += $totalUe;
//
//            if(!$elim && $ueInfo->moyenne >= 10){
//                $ueInfo->mention = 'Validé';
//                $ueInfo->creditObt = $ueInfo->creditTot;
//                $nbUeValid +=1;
//            }
//            else{
//                $ueInfo->mention = 'Non Validé';
//            }
//            $ueInfo->save();
//            $creditObtsem += $ueInfo->creditObt;
//        }
//
//        $semestreInfo->moyenne = $totalSem/30;
//        $semestreInfo->creditObt = $creditObtsem;
//        $semestreInfo->nbUeValid = $nbUeValid;
//        $semestreInfo->totalNotes = $totalSem;
//
//        if(!$elimSemestre && $semestreInfo->moyenne >= 10){
//            if($nbUeValid == sizeof($ues)){
//                $semestreInfo->mention = 'Validé';
//            }
//            elseif(sizeof($ues) > $nbUeValid && (sizeof($ues) - $nbUeValid) ==1){
//                $semestreInfo->mention = 'Validé par Compensation';
//                $semestreInfo->creditObt = 30;
//                $semestreInfo->nbUeValid = sizeof($ues);
//            }
//            else{
//                $semestreInfo->mention = 'Non Validé';
//            }
//        }
//        else{
//            $semestreInfo->mention = 'Non Validé';
//        }
//        if($type == 'session1' && $semestreInfo->mention == 'Non Validé'){
//            $semestreInfo->session = 'session2';
//        }
//
//        $semestreInfo->save();
        // traitement des cas d'enjambement
        if ($type == 'session2' && $semestre->suffixe == 2){
            $this->setResultat($contrat, $semestre);
        }


        return redirect()->route('notes.deliberation',[$semestre->id, $contrat->specialite_id]);
    }

    protected function setResultat($contrat, $semestre){
        $resultat = $this->resultatNominatifsRepository->firstOrNew(['contrat_id' => $contrat->id]);

        if ($semestre->cycle_id != 3 && $semestre->cycle_id != 5){

            if ($semestre->cycle->niveau == 1){
                $credits = $contrat->semestre_infos->sum('creditObt');
                $nb_sem_val = $contrat->semestre_infos->where('credtiObt', 30)->count();

                /** L'apprenant a validé le semestre **/

                if ($nb_sem_val == 2){
                    $resultat->next_cycle_id = $contrat->cycle_id + 1;
                    $resultat->decision = 'Admis';
                    $resultat->save();
                }
                elseif($nb_sem_val < 2 && $credits >= 45){
                    if ($nb_sem_val == 1 || $contrat->semestre_infos->where('creditObt', '>=', 23)){
                        $resultat->next_cycle_id = $contrat->cycle_id + 1;
                        $resultat->decision = 'Enjambement';
                        $resultat->save();
                    }
                    else{
                        $resultat->next_cycle_id = $contrat->cycle_id;
                        $resultat->decision = 'Redouble';
                        $resultat->save();
                    }
                }
                else{
                    $resultat->next_cycle_id = $contrat->cycle_id;
                    $resultat->decision = 'Redouble';
                    $resultat->save();
                }
            }
            elseif ($semestre->cycle->niveau == 2){ /** L'apprenant est en licence 2 **/
                if($contrat->academic_year_id != $contrat->apprenant->academic_year_id && $contrat->apprenant->academic_year_id != 1){ /** anciens apprenants de Pigier */
                    $credits = $contrat->apprenant->semestre_infos->sum('creditObt');
                    $nb_sem_val = $contrat->apprenant->semestre_infos->where('creditObt', 30)->count();

                    if ($credits == 120){
                        $resultat->next_cycle_id = $contrat->cycle_id + 1;
                        $resultat->decision = 'Admis';
                        $resultat->save();
                    }
                    /**
                     * Anciens apprenant de licence 2 pouvant etre en situation d'enjambement
                     **/
                    elseif ($credits >= 90 && $nb_sem_val >= 2 && $contrat->apprenant->semestre_infos->where('creditObt', '>=', 15)->count() == 4){
                        /** Egal à 4 car deux semestres sont supposé avoir 30 credits */
                        $resultat->next_cycle_id = $contrat->cycle_id + 1;
                        $resultat->decision = 'Enjambement';
                        $resultat->save();
                    }
                    else{
                        $resultat->next_cycle_id = $contrat->cycle_id;
                        $resultat->decision = 'Redouble';
                        $resultat->save();
                    }
                }
            }
        }
    }

    public function rattrapage($sem, $spec){

        $semestre = $this->semestreRepository->findWithoutFail($sem);
        $specialite = $this->specialiteRepository->findWithoutFail($spec);


        $app = $this->contratRepository->findWhere(['specialite_id' => $specialite->id, 'cycle_id' => $semestre->cycle_id, 'academic_year_id' => $this->anneeAcademic->id]);

        $contrats = [];

        $enseignements= [];

        foreach($app as $contrat){
            $ens =[];
            $semestreInfo = $contrat->semestre_infos->where('semestre_id', $semestre->id)->first();
            if (!$semestreInfo){
                Flash::error('Veuillez deliberer tous les etudiants avant svp');
                return redirect()->back();
            }

            if($semestreInfo->mention == 'Non Validé'){
                $contrats[] = $contrat;
                foreach($contrat->ue_infos as $ueInfo){
                    if($ueInfo->mention == 'Non Validé'){
                        foreach ($contrat->notes as $note) {
                            if ($note->enseignement->ue_id == $ueInfo->ue_id && $note->del1 < 10) {
                                $ens[$note->enseignement->ecue->title] = $note->enseignement;
                            }
                        }
                        $enseignements[$ueInfo->ue->title] = $ens;
                    }
                }
            }
            $enseignements[$contrat->id] = $ens;
        }
//        dd($enseignements);
        return view('notes.rattrapage', compact('contrats', 'enseignements'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($type, $enseignement, Request $request)
    {
        $input = $request->except('_token', 'DataTables_Table_0_length');
//        dd($input);
        $enseignement    = $this->enseignementRepository->findWithoutFail($enseignement);
        foreach($input as $key => $value){
            $contrat = $this->contratRepository->findWithoutFail($key);

            $note = $this->noteRepository->updateOrCreate(
                ['enseignement_id' => $enseignement->id, 'contrat_id' => $key],
                [$type => ($value != null) ? $value : 0]
            );
            if($type != 'cc'){
                if($type == 'session1'){
                    $note->del1 = $note->cc*0.4 + $note->session1*0.6;
//                    ($note->del1 >=10) ? $note->del2 = $note->del1 : '';
                    $note->save();
                }
                elseif($type == 'session2'){
                    $note->del2 = $note->cc*0.4 + $note->session2*0.6;
                    $note->save();
                }
            }

        }
        return redirect()->route('notes.affiche', [$enseignement->ecue->semestre->id, $note->contrat->specialite->id]);
    }

    public function getNoteContrat($contrat, $enseignement){
        $note = $this->noteRepository->findWhere(['enseignement_id' => $enseignement, 'contrat_id' => $contrat]);
        return response()->json($note);
    }

    /**
     * Afficher la page où l'on va renseigner les notes obtenues par les etudiants dans l'ecue choisies.
     *
     * @param  int  $id reprensente l'id de l'enseignement choisi
     * @return \Illuminate\Http\Response
     */
    public function show($type, $id)
    {
        $enseignement = $this->enseignementRepository->findWithoutFail($id);
        $specialite = $enseignement->specialite->id;
        $cycle = $enseignement->ecue->semestre->cycle->id;

        $contrats = $this->contratRepository->findWhere(['specialite_id' => $specialite, 'cycle_id' => $cycle, 'academic_year_id' => $this->anneeAcademic->id]);

        $ccComp = false;

        foreach($contrats as $contrat){
            if(!$contrat->notes->where('enseignement_id', $enseignement->id)->first() && $type != 'cc')
                $ccComp = true;
        }

        if($ccComp){
            Flash::error('Un ou plusieurs apprenants n\'ont pas de note de CC');
            return redirect()->back();
        }

        return view('notes.show', compact('enseignement', 'contrats' , 'type'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    public function imprime($sem, $specialite){
        $semestre = $this->semestreRepository->findWithoutFail($sem);
        $contrats = $this->contratRepository->findWhere(['specialite_id' => $specialite, 'cycle_id' => $semestre->cycle_id, 'academic_year_id' => $this->anneeAcademic->id]);

        return view('notes.imprime', compact('contrats', 'semestre'));
    }

    public function releve($session, $contrat, $semestre){

        $academicYear = $this->anneeAcademic;

        $contrat = $this->contratRepository->findWithoutFail($contrat);

        $semestre = $this->semestreRepository->findWithoutFail($semestre);

        $enseignements = $semestre->enseignements->where('specialite_id', $contrat->specialite_id)->where('academic_year_id', $this->anneeAcademic->id);

        $ues = [];

        foreach ($enseignements as $enseignement) {
            $ues[$enseignement->ue_id] = $enseignement->ue;
        }


        return view('notes.rnr_imprime', compact('contrat', 'semestre', 'enseignements', 'ues', 'academicYear', 'session'));
    }

    public function pv($sem, $spec, $session){

        $cycle = $this->semestreRepository->findWithoutFail($sem)->cycle;
//        $contrats = $this->contratRepository->findWhere(['specialite_id' => $spec, 'cycle_id' => $cycle->id, 'academic_year_id' => $this->anneeAcademic->id]);
        $contrats = Contrat::join('apprenants', 'apprenant_id', '=', 'apprenants.id')
            ->select('contrats.*')
            ->where('specialite_id', $spec)
            ->where('cycle_id', $cycle->id)
            ->where('contrats.academic_year_id', $this->anneeAcademic->id)
            ->orderBy('apprenants.nom')
            ->orderBy('apprenants.prenom')
            ->get();

        $semestre = $this->semestreRepository->findWithoutFail($sem);
        $specialite = $this->specialiteRepository->findWithoutFail($spec);
        $ecues =[];
        $academicYear = $this->anneeAcademic;
        $ec = $specialite->ecues->where('semestre_id', $sem);
        foreach($ec as $ecue){
            $ecues[] = $ecue->id;
        }
        $enseignements = $specialite->enseignements->whereIn('ecue_id', $ecues)->where('academic_year_id', $this->anneeAcademic->id);
        $ues = [];
        foreach($enseignements as $enseignement){
            $ues[$enseignement->ue->id] = $enseignement->ue;
        }
//        dd($enseignements);

        foreach($contrats as $contrat){
            $this->saveNotes($contrat, $enseignements, $session, $sem);
        }

        return view('notes.pv', compact('contrats', 'enseignements', 'ues', 'semestre', 'academicYear', 'session'));
    }

    public function pvcc($sem, $spec){
        $specialite = $this->specialiteRepository->findWithoutFail($spec);
        $semestre = $this->semestreRepository->findWithoutFail($sem);
        $cycle = $this->semestreRepository->findWithoutFail($semestre->id)->cycle;
        $contrats = Contrat::join('apprenants', 'apprenant_id', '=', 'apprenants.id')
            ->select('contrats.*')
            ->where('specialite_id', $spec)
            ->where('cycle_id', $cycle->id)
            ->where('contrats.academic_year_id', $this->anneeAcademic->id)
            ->orderBy('apprenants.nom')
            ->orderBy('apprenants.prenom')
            ->get();

        $ecues =[];
        $academicYear = $this->anneeAcademic;
        $ec = $specialite->ecues->where('semestre_id', $sem);
        foreach($ec as $ecue){
            $ecues[] = $ecue->id;
        }
        $enseignements = $specialite->enseignements->whereIn('ecue_id', $ecues)->where('academic_year_id', $academicYear->id);

        return view('notes.pvcc', compact('contrats', 'enseignements', 'academicYear', 'semestre'));
    }

    /**
     * cette fonction est une fonction interne qui permettra d'enregistrer les
     * informations sur le semestre de l'etudiant
     *
     *
     */
    protected function saveNotes($contrat, $enseignements, $session, $semestre){
        $semestreInfo = $this->semestreInfoRepository->firstOrNew([
            'semestre_id'=>$semestre,
            'contrat_id' => $contrat->id
        ]);
        $semestreInfo->session = $session;
        $elimSemestre = false;
        $creditObtsem = 0;
        $nbUeValid = 0;
        $totalSem = 0;

        $ues = [];

        foreach ($enseignements as $enseignement){
            $ues[$enseignement->ue_id] = $enseignement->ue;
        }

        foreach ($ues as $ue){
            $ueInfo = $this->ueInfoRepository->firstOrNew(['ue_id' => $ue->id, 'contrat_id' => $contrat->id]);
            $elim = false;
            $creditTot = $enseignements->where('ue_id', $ue->id)->sum('credits');
            $creditObt = 0;
            $totalUe = 0;
            //en fonction des notes enjambement. lorsque ce sera géré.
            $note = 0;

            foreach ($enseignements->where('ue_id', $ue->id) as $enseignement){
                $note = ($session == 'session1') ? $contrat->notes->where('enseignement_id', $enseignement->id)->first()->del1 : $contrat->notes->where('enseignement_id', $enseignement->id)->first()->del2;
                if ($session == 'session1'){
                    $note = $contrat->notes->where('enseignement_id', $enseignement->id)->first()->del1 ;
                }
                elseif($session == 'session2'){
                    $note = $contrat->notes->where('enseignement_id', $enseignement->id)->first()->del2;
                }
                elseif($session == 'enjambement'){
                    $note = $contrat->notes->where('enseignement_id', $enseignement->id)->first()->enjambement;
                }
                $totalUe += $note * $enseignement->credits;
                if ($note < 5){
                    $elim = $elimSemestre = true;
                }
                if($note >= 10) {
                    $creditObt += $enseignement->credits;
                }

            }

            $ueInfo->creditObt = $creditObt;
            $ueInfo->creditTot = $creditTot;
            $ueInfo->moyenne = $totalUe / $ueInfo->creditTot;
            $ueInfo->totalNotes = $totalUe;

            $totalSem += $totalUe;

            if(!$elim && $ueInfo->moyenne >= 10){
                $ueInfo->mention = 'Validé';
                $ueInfo->creditObt = $ueInfo->creditTot;
                $nbUeValid +=1;
            }
            else{
                $ueInfo->mention = 'Non Validé';
            }
            $ueInfo->save();
            $creditObtsem += $ueInfo->creditObt;
        }

        $semestreInfo->moyenne = $totalSem/30;
        $semestreInfo->creditObt = $creditObtsem;
        $semestreInfo->nbUeValid = $nbUeValid;
        $semestreInfo->totalNotes = $totalSem;

        /*
         * Si une ou plusieurs unités d'enseignements n'ont pas obtenus de note eliminatoire,
         * on verifie que l'apprenant a valider au moins (n-1) unités d'enseignement du semestre
         * le cas echeant le semestre est considéré comme non validé
         */
        if(!$elimSemestre && $semestreInfo->moyenne >= 10){
            if($nbUeValid == sizeof($ues)){
                $semestreInfo->mention = 'Validé';
            }
            elseif(sizeof($ues) > $nbUeValid && (sizeof($ues) - $nbUeValid) ==1){
                $semestreInfo->mention = 'Validé par Compensation';
                $semestreInfo->creditObt = 30;
                $semestreInfo->nbUeValid = sizeof($ues);
            }
            else{
                $semestreInfo->mention = 'Non Validé';
            }
        }
        else{
            $semestreInfo->mention = 'Non Validé';
        }
        if($session == 'session1' && $semestreInfo->mention == 'Non Validé'){
            $semestreInfo->session = 'session2';
        }

        $semestreInfo->save();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

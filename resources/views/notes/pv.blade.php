<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Proces verbal de deliberation</title>

    <link rel="stylesheet" href="{{ url('css/bootstrap.css') }}">
    <style>
        div .logo{
            /*width: 40%;*/
            /*height: 45%;*/
        }

        @media print{
            .footer{
                position: absolute;
                bottom: 0;
                width: 100%;
                margin-bottom: 150px;
            }
            .content-wrapper{
                -webkit-print-color-adjust: exact;
            }

        }
        .header {
            /*font-size: 80%;*/
            margin-bottom: 30px;
        }

        body{
            margin-top: 10px;
            margin-right: 20px;
        }

        th.vertical{
            white-space: nowrap;
            height: 200px;
        }
        th.vertical>div{
            transform:
                /*translate(25px, 51px)*/
                rotate(270deg);
            margin-bottom: 20px;
            width: 40px;
        }

        table{
            text-align: center;
            margin-top: 40px;
        }
        th.ref{
            /*width: 80px !important;*/
        }

        .table thead tr{
            font-size: 14px;
        }

        .table tr{
            font-size: 16px;
            padding: 5px 0px;
        }

        div.header{
            width: 2400px;
            margin-bottom: 10px;
        }

        body{
            padding-left: 20px;
        }

        .pv{
            display: flex;
            justify-content: space-between;
        }
        .pv>*{
            margin: auto 0px;
        }

    </style>
</head>

<body class="skin-blue sidebar-mini fixed">

<div class="wrapper container-fluid">
    <div class="content-wrapper ">

        <div class="header row fixed-top">
            <div class="col-xs-3 logo">
                <div><img src="{{ url('images/logo_pigier.jpg') }}" alt=""></div>
            </div>
            <div class="col-xs-6">
                <h1 class="text-center">PROCES VERBAL DE DELIBERATION</h1>
                <h1 class="text-center">Résultat des Examens {{ $semestre->title. ' ' .$session }}</h1>
                <h1 class="text-center">Domaine : Science Economique et de Gestion</h1>
                <h1 class="text-center">Science de gestion</h1>
                <h1 class="text-center">Spécialité : {{ $contrats->first()->specialite->slug }}</h1>
            </div>
            <div class="col-xs-3 pull-right">
                <div>
                    <h3>Réf: PIG/RFO/F/029</h3>
                    <h3>Version: 1.0</h3>
                </div>
            </div>
        </div>
        <div class="row pv">
            <table class="table table-bordered table-striped table-condensed">
                <thead>
                    <tr>
                        {{--<th rowspan="5" class="vertical"><div><span>effectif</span></div></th>--}}
                        <th rowspan="5" class="vertical ref"><div><span>ref. contrat</span></div></th>
                        <th rowspan="5" class=""><h4><span>Nom et Prenom</span></h4></th>
                    </tr>
                    <tr > {{-- Affichage des entete d'unite d'enseignement --}}
                        @foreach($ues as $ue)
                            <th class="text-center" colspan="{{ 2*$enseignements->where('ue_id', $ue->id)->count() +2 }}">{{ $ue->code.$contrats->first()->specialite->id.$semestre->id. ': ' .$ue->title }}</th>
                        @endforeach
                        <th>{{ sizeof($ues) }}</th>
                        <th rowspan="4" class="vertical bg-info"><div><span>Moyenne Semestrielle</span></div></th>
                        <th colspan="3">Resultat Semestre</th>
                    </tr>
                    <tr class="info">
                        @foreach($ues as $ue)
                            @foreach($enseignements as $enseignement)
                                @if($enseignement->ue_id == $ue->id)
                                    <th>Ecue</th>
                                    <th>Credit</th>
                                @endif
                            @endforeach
                            <th>Total</th>
                            <th rowspan="3" class="vertical"><div><span>Validation UE</span></div></th>
                        @endforeach
                        <th rowspan="3" class="vertical"><div><span>UE Validées | UE à Valider</span></div></th>
                        <th rowspan="3" class="vertical"><div><span>Validé</span></div></th>
                        <th rowspan="3" class="vertical"><div><span>Validé par Compensation</span></div></th>
                        <th rowspan="3" class="vertical"><div><span>Non Validé</span></div></th>
                    </tr>
                    <tr class="bg-warning">
                        @foreach($ues as $ue)
                            @foreach($enseignements as $enseignement)
                                @if($enseignement->ue_id == $ue->id)
                                    <th class="vertical"><div><p>{!! wordwrap($enseignement->ecue->title, 30, '<br />', true) !!}</p></div></th>
                                    <th>{{ $enseignement->credits }}</th>
                                @endif
                            @endforeach
                            <th>{{ $enseignements->where('ue_id', $ue->id)->sum('credits') }}</th>
                        @endforeach
                    </tr>
                    <tr>
                        @foreach($ues as $ue)
                            @foreach($enseignements as $enseignement)
                                @if($enseignement->ue_id == $ue->id)
                                    <th>Note</th>
                                    <th>Pond</th>
                                @endif
                            @endforeach
                            <th>Moy</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($contrats as $contrat)
                    <tr>
                        {{--<td>{{ ++$i }}</td>--}}
                        <td>{{ $academicYear->fin. '-' .$contrat->id }}</td>
                        <td>{{ $contrat->apprenant->nom. ' ' .$contrat->apprenant->prenom }}</td>
                        @foreach($ues as $ue)
                            @foreach($enseignements as $enseignement)
                                @if($enseignement->ue_id == $ue->id)
                                    @if($session == 'session1')
                                        <td>
                                            {!! $contrat->notes->where('enseignement_id', $enseignement->id)->first()->del1 !!}
                                        </td>
                                        <td>
                                            {!! $contrat->notes->where('enseignement_id', $enseignement->id)->first()->del1 * $enseignement->credits !!}
                                        </td>
                                    @elseif($session == 'session2')
                                        <td>
                                            {!! $contrat->notes->where('enseignement_id', $enseignement->id)->first()->del2 !!}
                                        </td>
                                        <td>
                                            {!! $contrat->notes->where('enseignement_id', $enseignement->id)->first()->del2 * $enseignement->credits !!}
                                        </td>
                                    @elseif($session == 'enjambement')
                                        <td>
                                            {!! $contrat->notes->where('enseignement_id', $enseignement->id)->first()->enjambement !!}
                                        </td>
                                        <td>
                                            {!! $contrat->notes->where('enseignement_id', $enseignement->id)->first()->enjambement * $enseignement->credits !!}
                                        </td>
                                    @endif
                                @endif
                            @endforeach
                            <td>
                                {{ $contrat->ue_infos->where('ue_id', $ue->id)->first()->moyenne }}
                            </td>

                                @if($contrat->ue_infos->where('ue_id', $ue->id)->first()->mention == 'Validé')
                                    <td class="bg-success">
                                        V
                                    </td>
                                @else
                                    <td class="bg-danger">
                                        NV
                                    </td>
                                @endif
                        @endforeach
                        <td>{{ $contrat->semestre_infos->where('semestre_id', $semestre->id)->first()->nbUeValid }}</td>
                        <td>{{ $contrat->semestre_infos->where('semestre_id', $semestre->id)->first()->moyenne }}</td>
                        <td>
                            @if($contrat->semestre_infos->where('semestre_id', $semestre->id)->first()->mention == 'Validé')
                                X
                            @endif
                        </td>
                        <td>
                            @if($contrat->semestre_infos->where('semestre_id', $semestre->id)->first()->mention == 'Validé par Compensation')
                                X
                            @endif
                        </td>
                        <td>
                            @if($contrat->semestre_infos->where('semestre_id', $semestre->id)->first()->mention == 'Non Validé')
                                X
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="footer">
            <div class="row">
                <h4 class="text">Fait à Douala, le ...... / ...... /.......</h4>
            </div>
            <div class="row">
                <div class="col-xs-3 pull-left">
                    <h4 class="text-center"><strong>Le President du Jury</strong></h4>
                </div>
                <div class="col-xs-3 col-xs-offset-2">
                    <h4 class="text-center"><strong>Le Coordonateur</strong></h4>
                </div>
                <div class="col-xs-3 pull-right">
                    <h4 class="text-center"><strong>Le Rapporteur</strong></h4>
                </div>
            </div>
        </div>

    </div>
{{--    <div class="row">--}}
{{--        <button class="btn btn-primary" onclick="imprimer('rnr')">Imprimer</button>--}}
{{--    </div>--}}
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js" integrity="sha384-aJ21OjlMXNL5UyIl/XNwTMqvzeRMZH2w8c5cRVpzpU8Y5bApTppSuUkhZXN0VxHd" crossorigin="anonymous"></script>
<script>
    function imprimer(rnr){
        var printContents = document.getElementById(rnr).innerHTML;
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
    }
</script>
</body>

</html>
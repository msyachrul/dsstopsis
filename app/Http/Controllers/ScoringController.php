<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Alternative;
use App\Criteria;
use App\Score;

class ScoringController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    { 
        $alternatives = Alternative::has('scores')->get();
        $criterias = Criteria::all();

        if (count(Alternative::has('scores')->get()) < 2) {
            return redirect()->route('score.index')->with('info','Belum ada nilai yang diinputkan');
        }

        $sums = [];
        $squares = [];
        $normalizations = [];
        $normalizationWeights = [];
        $solusiPlus = [];
        $solusiMinus = [];
        $_dPlus = [];
        $_dMinus = [];
        $result = [];

        foreach ($criterias as $key => $criteria) {
            $sums[$key] = 0;
            $squares[$key] = 0;
            foreach ($criteria->scores as $k => $score) {
                $_score = pow($score->score,2);
                $sums[$key] = $sums[$key] + $_score;
            }
            $squares[$key] = sqrt($sums[$key]);
        }

        foreach ($alternatives as $key => $alternative) {
            $normalizations[$key] = [];
            $normalizationWeights[$key] = [];
            foreach ($alternative->scores as $k => $score) {
                $normalizations[$key][$k] = $score->score/$squares[$k];
                $normalizationWeights[$key][$k] = ($score->score/$squares[$k])*$criterias[$k]->weight;
            }
        }

        for ($i=0; $i < count(current($normalizationWeights)); $i++) { 
            $solusiPlus[] = max(array_column($normalizationWeights, $i));
            $solusiMinus[] = min(array_column($normalizationWeights, $i));
        }

        foreach ($normalizationWeights as $key => $normalizationWeight) {
            $_dPlus[$key] = [];
            $_dMinus[$key] = [];
            foreach ($normalizationWeight as $k => $nW) {
                $_dPlus[$key][] = pow($nW-$solusiPlus[$k],2);
                $_dMinus[$key][] = pow($nW-$solusiMinus[$k],2);
            }
        }

        foreach ($alternatives as $key => $alternative) {
            $result[$key] = [];
            $result[$key]['data'] = $alternative->name;
            $result[$key]['dMinus'] = sqrt(array_sum($_dMinus[$key]));
            $result[$key]['dPlus'] = sqrt(array_sum($_dPlus[$key]));
            $result[$key]['v'] = $result[$key]['dMinus']/($result[$key]['dMinus']+$result[$key]['dPlus']);
        }

        // mengurutkan berdasarkan v terbesar
        uasort($result, function ($a,$b) {
            return $a['v'] < $b['v'];
        });

        return view('scoring.index')->with([
            'alternatives' => $alternatives,
            'criterias' => $criterias,
            'squares' => $squares,
            'normalizations' => $normalizations,
            'normalizationWeights' => $normalizationWeights,
            'solusiPlus' => $solusiPlus,
            'solusiMinus' => $solusiMinus,
            'result' => $result,
        ]);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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

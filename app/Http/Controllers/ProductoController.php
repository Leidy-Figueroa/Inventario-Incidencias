<?php

namespace App\Http\Controllers;


use App\Models\Producto;
use App\Models\Estado;
use App\Models\Marca;
use App\Models\Modelo;
use App\Models\User;
use App\Models\Base;
use App\Models\Codigo;
use App\Models\Incident;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductosExport;
Use Auth;
use Hash;
Use Carbon\Carbon;
Use Carbon\CarbonInterface;


/**
 * Class ProductoController
 * @package App\Http\Controllers
 */
class ProductoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getProducto(Request $request,producto $productos) {

       $productos = Producto::withTrashed()->get();

       return view('producto.index', compact('productos'))->with('productos',$productos);
    }
    public function getProductoCreate() {

        $productos=Producto::all(['id','caracteristicas','codigos_id', 'precio_devaluado', 'vencimiento', 'precio','fecha_ingreso', 'fecha_vencimiento', 'fecha_compra','user_id',  'imagen','serie','descripcion','estado_id','marca_id','modelo_id','departamento_id']);
        $marcas=Marca::all(['id','nombre']);
        $modelos=Modelo::all(['id','nombre']);
        $estados=Estado::all(['id','nombre']);
        $users=User::all(['id','name']);
        $codigos=Codigo::all(['id', 'nombre', 'numero']);
        $departamentos=Departamento::all(['id','nombre']);

        return view('producto.create')->with('productos',$productos)->with('codigos',$codigos)->with('marcas',$marcas)->with('modelos',$modelos)->with('estados',$estados)->with('users',$users)->with('departamentos',$departamentos);
    }

     public function postProducto(Request $request, producto $productos) 
     {
     $data = $request->validate([
        'serie' => 'required|max:16|min:8|unique:productos',
        'caracteristicas'=>'required',
        'codigos_id'=>'required',
        'fecha_compra'=>'required',
        'user_id'=>'required',
        'precio' => 'required',
        'marca'=>'required',
        'fecha_vencimiento' => 'required',
        'modelo'=>'required',
        'descripcion'=>'required',
        'estado'=>'required',
        'imagen' => 'required',
        'departamento' => 'required'
    ],[
        'serie.required'=>'El n??mero de serie es obligatorio',
        'caracteristicas.required'=>'Las caracter??sticas son obligatorias',
        'serie.unique'=>'La serie ya esta en uso',
        'marca.required'=>'La marca es obligatoria',
        'codigos_id.required'=>'La codigo es obligatorio',
        'fecha_compra.required'=>'La fecha de compra es obligatoria',
        'user_id.required'=>'El custodio es obligatorio',
        'precio.required'=>'El precio es obligatorio',
        'modelo.required'=>'El modelo es obligatorio',
        'descripcion.required'=>'El nombre es obligatorio',
        'estado.required'=>'El estado es obligatorio',
        'departamento.required'=>'El departamento es obligatoria',
        'imagen.required' => 'Subir imagen del producto'
    ]
    );
        $ruta_imagen = $request['imagen']->store('imagenes','public');
        $url = Storage::url($ruta_imagen);

        DB::table('productos')->insert([
            'serie'=>$data['serie'],
            'codigos_id'=>$data['codigos_id'],
            'caracteristicas'=>$data['caracteristicas'],
            'fecha_ingreso'=>date('Y-m-d H:i:s'),
            'fecha_compra'=>$data['fecha_compra'],
            'user_id'=>$data['user_id'],
            'precio'=>$data['precio'],
            'descripcion'=>$data['descripcion'],
            'estado_id'=>$data['estado'],
            'modelo_id'=>$data['modelo'],
            'marca_id'=>$data['marca'],
            "departamento_id"=>$data['departamento'],
            "imagen"=>$ruta_imagen
            
        ]); 
        return back()->with('notification', 'Producto registrado existosamente.');
    }
    public function postProductoCreate(Request $request, producto $productos) 
    {
        $data = $request->validate([
            'serie' => 'required|max:16|min:8|unique:productos',
            'caracteristicas'=>'required',
            'codigos_id'=>'required',
            'fecha_compra'=>'required',
            'user_id'=>'required',
            'precio' => 'required',
            'marca'=>'required',
            'modelo'=>'required',
            'descripcion'=>'required',
            'estado'=>'required',
            'imagen' => 'required',
            'departamento' => 'required'
        ],[
            'serie.required'=>'El n??mero de serie es obligatorio',
            'serie.unique'=>'La serie ya esta en uso',
            'caracteristicas.required'=>'Las caracter??sticas son obligatorias',
            'codigos_id.required'=>'el codigo es obligatoria',
            'marca.required'=>'La marca es obligatoria',
            'fecha_compra.required'=>'La fecha de compra es obligatoria',
            'user_id.required'=>'El custodio es obligatoria',
            'precio.required'=>'El precio es obligatorio',
            'modelo.required'=>'El modelo es obligatorio',
            'descripcion.required'=>'El nombre es obligatorio',
            'estado.required'=>'El estado es obligatorio',
            'departamento.required'=>'El departamento es obligatoria',
            'imagen.required' => 'Subir imagen del producto'
        ]
        );
            $ruta_imagen = $request['imagen']->store('imagenes','public');
            $url = Storage::url($ruta_imagen);
        
        DB::table('productos')->insert([
            'serie'=>$data['serie'],
            'codigos_id'=>$data['codigos_id'],
            'caracteristicas'=>$data['caracteristicas'],
            'fecha_ingreso'=>date('Y-m-d H:i:s'),
            'fecha_compra'=>$data['fecha_compra'],
            'user_id'=>$data['user_id'],
            'precio'=>$data['precio'],
            'descripcion'=>$data['descripcion'],
            'estado_id'=>$data['estado'],
            'modelo_id'=>$data['modelo'],
            'marca_id'=>$data['marca'],
            "departamento_id"=>$data['departamento'],
            "imagen"=>$ruta_imagen,
        ]);

    $dt = Carbon::now();
    $todayDate = $dt->toDayDateTimeString();
    $responsable = Auth::User();
            
    $activityLog = [
        'responsable'	    => $responsable->name,
        'descripcion'		=> $data['descripcion'],
        'serie'			    => $data['serie'],
        'departamento_id'	=> $data['departamento'],
        'modyfy_user'		=> 'CREATE_PRODUCT',
        'date_time'         => $todayDate
    ];

    DB::table('producto_activity_logs')->insert($activityLog);
    return back()->with('notification', 'Producto registrado existosamente.');
    
    }
    public function show($id,  Request $request)
    {
        Carbon::setLocale('es');
        $producto = Producto::find($id);
        //FECHA DE COMPRA
        $fecha_compra = $producto->fecha_compra;
        $fecha_vencimiento = $producto->fecha_vencimiento;
    
        $A = $producto->precio;

        if ($fecha_vencimiento === null){
        $producto = Producto::find($id);
        $fecha_compra = $producto->fecha_compra;
        $A = new Carbon ($fecha_compra);

        //Guardar fecha_vencimiento (3 a??os m??s al de la compra) 
        $fecha_vencimiento = $A->addYear(3);
        $producto->fecha_vencimiento = $fecha_vencimiento;
        $producto->update();
        }
        //FECHA ACTUAL
        $fecha_actual =  Carbon::now();
        //FECHA VENCIMIENTO
        $fecha_vencimiento = $producto->fecha_vencimiento;

        $fecha_compra = Carbon::createFromFormat('Y-m-d H:i:s',  $fecha_compra);
        $fecha_vencimiento  = Carbon::createFromFormat('Y-m-d H:i:s',  $fecha_vencimiento);
        $fecha_actual=new Carbon('today');

        $totalA??os = $fecha_compra->diffInYears($fecha_vencimiento); #A??os restantes
        $totalDays = $fecha_compra->diffInDays($fecha_vencimiento);  #D??as restantes
        $diffDays = $fecha_vencimiento->diffInYears($fecha_actual);  #D??as que faltan para que termine
        $pastDays = $fecha_compra->diffInYears($fecha_actual);       #D??as ya transcurridos
        $msgInfo= $fecha_vencimiento == $fecha_actual ? "Terminado": "Faltan $totalA??os a??os y $diffDays d??as. Han transcurrido $pastDays de $totalDays d??as totales";
        
        //return dd( $msgInfo) ;

        if ($fecha_actual->diffInYears($fecha_vencimiento,false) <=0){
            $contador = "CADUCADO";
            $producto->vencimiento = $contador;
            $producto->update();
        }else{
            $contador = Carbon::parse($fecha_vencimiento)->diffInYears($fecha_actual,[
                'options' => Carbon::JUST_NOW,
                'syntax' => CarbonInterface::DIFF_ABSOLUTE,
                'parts' => 3,
                'short' => true,  
            ]);
        
            $producto->vencimiento = $contador;
            $producto->update();

            //return dd($contador);
            }
           
            if ($producto->vencimiento >= 3 ){
                $producto->precio_devaluado = $producto->precio;
                $producto->update();
            }
            if ($producto->vencimiento === 2){
                $producto->precio_devaluado = null;
                $producto = Producto::find($id);
                $A = $producto->precio;
                $porcentaje = $A / 3; 
                $B = $producto->precio - $porcentaje;
                $producto->precio_devaluado = $B;
                $producto->update();
            }
            if ($producto->vencimiento === 1){
                $producto->precio_devaluado = null;
                $producto = Producto::find($id);
                $producto->update();
                $A = $producto->precio;
                $porcentaje = $A / 3; 
                $B = $producto->precio - $porcentaje;
                $C = $B - $porcentaje;
                $producto->precio_devaluado = $C;
                $producto->update();
            }
            if ($producto->vencimiento === "CADUCADO"){
                $producto->precio_devaluado = null;
                $producto = Producto::find($id);
                $producto->update();
                $A = $producto->precio;
                $producto->precio_devaluado = $A - $A;
                $producto->update();
            }
        return view('producto.show', compact('producto', 'contador'));
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function edit($id, Request $request)
    {
            $producto = Producto::findOrFail($id);
            $marcas=Marca::all(['id','nombre']);
            $modelos=Modelo::all(['id','nombre']);
            $estados=Estado::all(['id','nombre']);
            $incidents=incident::all(['id','support_id','title']);
            $users=user::all(['id','name']);
            $codigos=Codigo::all(['id','nombre', 'numero']);
            $departamentos=Departamento::all(['id','nombre']);

            return view('producto.edit')->with(compact('codigos','producto', 'marcas','modelos','estados','incidents','users', 'departamentos'));
    }

    public function update($id, Request $request)
    {
        $data = $request->validate([
            'fecha_compra'=>'required',
            'caracteristicas'=>'required',
            'codigos_id'=>'required',
            'user_id'=>'required',
            'precio'=>'required',
            'marca'=>'required',
            'modelo'=>'required',
            'descripcion'=>'required',
            'estado'=>'required',
            'departamento' => 'required'
        ],[
            'serie.unique'=>'La serie ya esta en uso',
            'caracteristicas.required'=>'Las caracteristicas son obligatorias',
            'codigos_id.required'=>'El codigo es obligatorio',
            'marca.required'=>'La marca es obligatoria',
            'fecha_compra.required'=>'La fecha de compra es obligatoria',
            'user_id.required'=>'El custodio es obligatoria',
            'modelo.required'=>'El modelo es obligatorio',
            'precio.required'=>'El precio es obligatorio',
            'descripcion.required'=>'El nombre es obligatorio',
            'estado.required'=>'El estado es obligatorio',
            'departamento.required'=>'El departamento es obligatoria',
        ]
       );

    	$producto = Producto::find($id);
        $producto->descripcion = $request->input('descripcion');
        $producto->caracteristicas = $request->input('caracteristicas');
        $producto->codigos_id = $request->input('codigos_id');
    	$producto->serie = $request->input('serie');
        $producto->fecha_compra = $request->input('fecha_compra');
        $producto->fecha_vencimiento = null;
        $producto->user_id = $request->input('user_id');
        $producto->precio = $request->input('precio');
        $producto->marca_id = $request->input('marca');
        $producto->estado_id = $request->input('estado');
        $producto->modelo_id = $request->input('modelo');
        $producto->departamento_id = $request->input('departamento');
    	$producto->save();
        if ($producto->vencimiento === "CADUCADO"){
            $producto = Producto::find($id);
            $producto->vencimiento = null;
            $producto->update();
            }

        //FECHA
        $dt = Carbon::now();
        $todayDate = $dt->toDayDateTimeString();
        $responsable = Auth::User();
    
        $serie = $producto->serie;
        $descripcion =  $producto->descripcion;
        $departamento = $producto->departamento_id;

        $dt = Carbon::now();
        $todayDate = $dt->toDayDateTimeString();
        $responsable = Auth::User();
   
   $activityLog = [
       'responsable'	    => $responsable->name,
       'descripcion'		=> $descripcion,
       'serie'			    => $serie,
       'departamento_id'	=> $departamento,
       'modyfy_user'		=> 'UPDATE_PRODUCT',
       'date_time'          => $todayDate
   ];
    DB::table('producto_activity_logs')->insert($activityLog);
    return back()->with('notification', 'Producto modificado exitosamente.');
    }

    public function delete($id, Request $request)
    {
        $producto = Producto::find($id);
        $producto->delete();

        $serie = $producto->serie;
        $descripcion =  $producto->descripcion;
        $departamento = $producto->departamento_id;

        $dt = Carbon::now();
        $todayDate = $dt->toDayDateTimeString();
        $responsable = Auth::User();
   
   $activityLog = [
       'responsable'	    => $responsable->name,
       'descripcion'		=> $descripcion,
       'serie'			    => $serie,
       'departamento_id'	=> $departamento,
       'modyfy_user'		=> 'DELETE_PRODUCT',
       'date_time'          =>  $todayDate
   ];
   DB::table('producto_activity_logs')->insert($activityLog);

        return back()->with('notification', 'El producto se ha deshabilitado correctamente.');
    }
    public function restore($id)
    {
        Producto::withTrashed()->find($id)->restore();
        
        return back()->with('notification', 'El producto se ha habilitado correctamente.');
    }
    public function imprimir(){
        $productos = Producto::get();
        $pdf = \PDF::loadView('producto.pdf', compact('productos'));
        return $pdf->download('ReporteGeneral.pdf');
    }
    public function exportExcel(){
        return Excel::download(new ProductosExport, 'productos.xlsx');
    }
    public function activityLog(Request $request){

        $name = $request->name;
        $departamentos=Departamento::all(['id','nombre']);
        $activityLog = DB::table('producto_activity_logs')
        ->where('responsable', 'like', '%' .$name. '%')
        ->get();

        return view('producto.producto_activity_log', compact('activityLog'))->with('departamentos',$departamentos);;
    }


}
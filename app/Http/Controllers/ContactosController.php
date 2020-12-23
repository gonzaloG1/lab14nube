<?php

namespace App\Http\Controllers;

use App\Models\Contactos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use JD\Cloudder\Facades\Cloudder;

class ContactosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $datos['contactos']=Contactos::paginate(5);
        return view('contactos.index',$datos);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('contactos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $datosUsuario=request()->except('_token');
        
 //---------------------- parte de cloudinary
        $this->validate($request,[
            'Foto'=>'required|mimes:jpeg,bmp,jpg,png|between:1, 6000',
        ]);

        $image = $request->file('Foto');
        $name = $request->file('Foto')->getClientOriginalName();
        $image_name = $request->file('Foto')->getRealPath();;
        Cloudder::upload($image_name, null);
        list($width, $height) = getimagesize($image_name);
        $image_url= Cloudder::show(Cloudder::getPublicId(), ["width" => $width, "height"=>$height]);
        //temporal la de abajo--> obtiene el nombre de la imagen
        $image_name_un= Cloudder::getPublicId();

        //temporal para una consulta de un dato
        //$valoress = contactos::find('Nombre')->where('id','17')->first();
       /*  $valoress = productos::where('id',5)
        ->firstOr(['Nombre_foto'],function(){}); */
        
        //save to uploads directory
        $image->move(public_path("uploads"), $name);
                
        //obetner valores individualmente
        $nombre_nuevo = $request->input('Nombre');
        $apellido_nuevo = $request->input('Apellido');
        $correo_nuevo = $request->input('Correo');
        $numero_nuevo = $request->input('Numero');
        
            //insertar
        contactos::insert([
        'Nombre'  =>$nombre_nuevo ,
        'Apellido' => $apellido_nuevo,
        'Correo' =>$correo_nuevo ,
        'Numero' => $numero_nuevo, 
        'Foto' => $image_url,
        'Nombre_foto'=>$image_name_un]);

        // anterior        contactos::insert($datosUsuario);

        return redirect('contactos')->with('Mensaje','Contacto almacenado  ');

        //
        //$datosProductos=request()->all();

        /* $datosProductos=request()->except('_token');
        
        if ($request->hasfile('Foto')){

            $datosProductos['Foto']=$request->file('Foto')->store('uploads','public');
        }

        Productos::insert($datosProductos);

        //return response()->json($datosProductos);
        return redirect('contactos')->with('Mensaje','Contacto registrado exitosamente'); */
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Contactos $contactos
     * @return \Illuminate\Http\Response
     */
    public function show(Contactos $contactos)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Contactos  $contactos
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $contacto=Contactos::findOrFail($id);

        return view('contactos.edit',compact('contacto'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Contactos $contactos
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
       /*  $datosProductos=request()->except(['_token','_method']);

        if ($request->hasfile('Foto')){

            $producto=Productos::findOrFail($id);

            Storage::delete('public/'.$producto->Foto);

            $datosProductos['Foto']=$request->file('Foto')->store('uploads','public');
        }

        Productos::where('id','=',$id)->update($datosProductos); */

        //$producto=Productos::findOrFail($id);
        //return view('productos.edit',compact('producto'));

        

        //return redirect('productos')->with('Mensaje','Producto modificado exitosamente');
        $datosUsuario=request()->except(['_token','_method']);
        //---------------img
        $this->validate($request,[
            'Foto'=>'required|mimes:jpeg,bmp,jpg,png|between:1, 6000',
        ]);
        $image = $request->file('Foto');
        $name = $request->file('Foto')->getClientOriginalName();
        $image_name = $request->file('Foto')->getRealPath();;
        Cloudder::upload($image_name, null);
        list($width, $height) = getimagesize($image_name);
        $image_url= Cloudder::show(Cloudder::getPublicId(), ["width" => $width, "height"=>$height]);
        $image_name_un= Cloudder::getPublicId();
        $image->move(public_path("uploads"), $name);    
        $nombre_nuevo = $request->input('Nombre');
        $apellido_nuevo = $request->input('Apellido');
        $correo_nuevo = $request->input('Correo');
        $numero_nuevo = $request->input('Numero');

        //elimina el dato de cloudinary--------------------------
        $valoress = contactos::where('id',$id)
        ->firstOr(['Nombre_foto'],function(){});
        //da formato
        $nombre_foto =$valoress->Nombre_foto;
        Cloudder::destroyImages($nombre_foto);
        //elimina----------------------------------------------

        contactos::where('id','=',$id)->update([
        'Nombre'  =>$nombre_nuevo ,
        'Apellido' => $apellido_nuevo,
        'Correo' =>$correo_nuevo ,
        'Numero' => $numero_nuevo, 
        'Foto' => $image_url,
        'Nombre_foto'=>$image_name_un]);

        $usuario= contactos::findOrFail($id);
        //return view('contactos.edit', compact('usuario'));
        return redirect('contactos')->with('Mensaje','Modificación de contacto correcto');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Contactos  $contactos
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //recoge el valor de imagen
    $valoress = contactos::where('id',$id)
    ->firstOr(['Nombre_foto'],function(){});
    //da formato
    $nombre_foto =$valoress->Nombre_foto;
    //eliminacloud
    Cloudder::destroyImages($nombre_foto);

    //elimina DB
    contactos::destroy($id);

    return redirect('contactos')->with('Mensaje','Contacto eliminado  ');
        /* //
        $producto=Productos::findOrFail($id);

        if(Storage::delete('public/'.$producto->Foto)){
            Productos::destroy($id);
        };

        return redirect('productos')->with('Mensaje','Producto eliminado exitosamente');; */
    }
}

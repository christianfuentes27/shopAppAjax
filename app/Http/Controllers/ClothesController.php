<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Clothes;
use App\Models\Image;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;

class ClothesController extends Controller
{
    const ITEMS_PER_PAGE = 3;
    const ORDER_BY = 'clothes.name';
    const ORDER_TYPE = 'asc';
    const PRICE_RANGE = 'all';
    const CATEGORY = 'all';
    const SIZE = 'any';
    
    private function getOrderBy($order) {
        return $this->getOrder($this->getOrderBys(), $order, self::ORDER_BY);
    }
    
    private function getOrder($orderArray, $order, $default) {
        $value = array_search($order, $orderArray);
        if(!$value) {
            return $default;
        }
        return $value;
    }

    private function getOrderBys() {
        return [
            'clothes.id'      => 'b1',
            'clothes.name'    => 'b2',
            'price'           => 'b3',
            'updated_at'      => 'b4'
        ];
    }
    
    private function getOrderType($order) {
        return $this->getOrder($this->getOrderTypes(), $order, self::ORDER_TYPE);
    }

    private function getOrderTypes() {
        return [
            'asc'  => 't1',
            'desc' => 't2',
        ];
    }
    
    private function getPriceRange($order) {
        return $this->getOrder($this->getPriceRanges(), $order, self::PRICE_RANGE);
    }
    
    private function getPriceRanges() {
        return [
            'all'     => 'p1',
            '0/49'    => 'p2',
            '50/99'   => 'p3',
            '100/149' => 'p4',
            '150/199' => 'p5',
            '200/249' => 'p6'
        ];
    }
    
    private function getCategories() {
        return [
            'all'   => 'c1',
            'man'   => 'c2',
            'woman' => 'c3'
        ];
    }
    
    private function getCategory($order) {
        return $this->getOrder($this->getCategories(), $order, self::CATEGORY);
    }
    
    private function getSizes() {
        return [
            'any' => 's1',
            'xs'  => 's2',
            's'   => 's3',
            'm'   => 's4',
            'l'   => 's5',
            'xl'  => 's6',
        ];
    }
    
    private function getSize($order) {
        return $this->getOrder($this->getSizes(), $order, self::SIZE);
    }
    
    private function getOrderUrls($oBy, $oType, $pRange, $category, $size, $q, $route) {
        $urls = [];
        $orderBys = $this->getOrderBys();
        $orderTypes = $this->getOrderTypes();
        $priceRanges = $this->getPriceRanges();
        $categories = $this->getCategories();
        $sizes = $this->getSizes();
        foreach($orderBys as $indexBy => $by) {
            foreach($orderTypes as $indexType => $type) {
                foreach($priceRanges as $indexPrice => $price) {
                    foreach($categories as $indexCategory => $categ) { 
                        foreach($sizes as $indexSize => $s) {
                            if($oBy == $indexBy && $oType == $indexType && $pRange == $indexPrice && $category == $indexCategory && $size == $indexSize) {
                                $urls[$indexBy .'/'. $indexType .'/'. $indexPrice .'/'. $indexCategory .'/'. $indexSize] = url()->full() . '#';
                            } else {
                                $urls[$indexBy .'/'. $indexType .'/'. $indexPrice .'/'. $indexCategory .'/'. $indexSize] = route($route, [
                                                                        'orderby'    => $by,
                                                                        'ordertype'  => $type,
                                                                        'pricerange' => $price,
                                                                        'category'   => $categ,
                                                                        'size'       => $s,
                                                                        'q'          => $q]);
                            }
                        }
                    }
                }
            }
        }
        return $urls;
    }
    
    public function fetchData(Request $request) {
        $q = $request->input('q', '');
        $orderby = $this->getOrderBy($request->input('orderby'));
        $ordertype = $this->getOrderType($request->input('ordertype'));
        $pricerange = $this->getPriceRange($request->input('pricerange'));
        $category = $this->getCategory($request->input('category'));
        $size = $this->getSize($request->input('size'));
        
        //construcción de la consulta
        
        // SELECT c.name producto
        // FROM `clothes_size` cs
        // JOIN `clothes` c ON cs.clothes_id=c.id
        // JOIN `size` s ON  cs.size_id=s.id
        // WHERE s.name='l';
        
        // Tallas
        if($size != self::SIZE) {
            $item = DB::table('clothes')
                        ->join('clothes_size', 'clothes.id', '=', 'clothes_size.clothes_id')
                        ->join('size', 'size.id', '=', 'clothes_size.size_id')
                        ->select('clothes.*')
                        ->where('size.name', '=', $size);
        } else {
            // Añadimos select sin tallas
            $item = DB::table('clothes')->select('clothes.*');
        }
        
        // Rangos de precios
        if($pricerange != self::PRICE_RANGE) {
            $range = explode("/", $pricerange);
            $minrange = intval($range[0]);
            $maxrange = intval($range[1]);
            $item = $item->where('clothes.price', '>=', $minrange)
                         ->where('clothes.price', '<=', $maxrange);
        }
        
        // Categoria hombre o mujer
        if($category != self::CATEGORY) {
            $item = $item->where('clothes.category', '=', $category);
        }
        
        // SELECT * FROM `clothes` 
        // WHERE price >= 20 AND price <= 94 AND name IN (SELECT name FROM `clothes` WHERE name LIKE '%an%' OR name LIKE '%repes%')
        // ORDER BY name DESC;
        
        // Consulta barra search coexistente con el resto
        if($q != '') {
            $arraysearch = DB::table('clothes')->select('*')->where('clothes.name', 'like', '%' . $q . '%')->get();
            $arraynames = [];
            foreach($arraysearch as $clothesname) {
                array_push($arraynames, $clothesname->name);
            }
            $item = $item->whereIn('clothes.name', $arraynames);
        }
        
        $item = $item->orderBy($orderby, $ordertype);
        if($orderby != self::ORDER_BY) {
            $item = $item->orderBy(self::ORDER_BY, self::ORDER_TYPE);
        }
        // dd($item);
        //ejecutar la consulta, usando la paginación
        $clothes = $item->paginate(self::ITEMS_PER_PAGE)->withQueryString();
        
        // Conversion de los productos a modelos para poder acceder a sus relaciones 
        $clothesmodel = [];
        foreach($clothes as $c) {
            $cmodel = new Clothes();
            $cmodel->id = $c->id;
            $cmodel->name = $c->name;
            $cmodel->category = $c->category;
            $cmodel->price = $c->price;
            $cmodel->thumbnail = $c->thumbnail;
            array_push($clothesmodel, $cmodel);
        }
        
        $sizes = [];
        foreach($clothesmodel as $model) {
            $sizes[$model->id] = $model->sizes;
        }
        
        return response()->json(['csrf'         => csrf_token(),
                                 'order'        => $this->getOrderUrls($orderby, $ordertype, $pricerange, $category, $size, $q, 'clothes.fetch'),
                                 'sizes'        => $sizes,
                                 'size'         => $size,
                                 'sizevalue'    => $request->input('size'),
                                 'category'     => $category,
                                 'categvalue'   => $request->input('category'),
                                 'pricerange'   => $pricerange,
                                 'pricevalue'   => $request->input('pricerange'),
                                 'ordertype'    => $ordertype,
                                 'typevalue'    => $request->input('ordertype'),
                                 'orderby'      => $orderby,
                                 'byvalue'      => $request->input('orderby'),
                                 'q'            => $q,
                                 'url'          => url('/'),
                                 'clothesmodel' => $clothesmodel,
                                 'clothes'      => $clothes], 200);
    }
    
    public function index() {
        return view('shop.index');
    }
    
    public function admin() {
        return view('admin.index');
    }
    
    public function getitemdata(Request $request) {
        $id = json_decode($request->getContent(), true);
        $item = Clothes::firstWhere('id', $id);
        $clothes = new Clothes();
        $clothes->id = $item->id;
        $clothes->name = $item->name;
        $clothes->category = $item->category;
        $clothes->price = $item->price;
        $clothes->thumbnail = $item->thumbnail;
        $item->sizes = $clothes->sizes;
        return response()->json($item);
    }
    
    public function create()
    {
        return view('shop.create');
    }
    
    public function store(Request $request)
    {
        try {
            $item = json_decode($request->getContent(), true);
            $clothes = new Clothes();
            $clothes->name = $item['name'];
            $clothes->price = $item['price'];
            $clothes->category = $item['category'];
            $clothes->thumbnail = $item['thumbnail'];
            $clothes->save();
            
            foreach($item['sizes'] as $namesize) {
                $size = Size::where('name', $namesize)->first();
                $data = array('clothes_id' => $clothes->id, 'size_id' => $size->id);
                DB::table('clothes_size')->insert($data);
            }
            
            return response()->json(['clothes' => $clothes]);
            
        } catch(\Exception $e) {
            return back()->withInput($request->all())->withErrors(
                ['default' => 'Something went wrong']);
        }
    }
    
    public function update(Request $request, Clothes $clothes)
    {
        try {
            $item = json_decode($request->getContent(), true);
            $clothes->name = $item['name'];
            $clothes->category = $item['category'];
            $clothes->price = $item['price'];
            $clothes->thumbnail = $item['thumbnail'];
            $clothes->updated_at = Carbon::now();
            $currentSizesIds = DB::table('clothes_size')->select('size_id')
                                    ->where('clothes_id', $clothes->id)->get();
            $newSizesObjectIds = DB::table('size')->select('id')->whereIn('name', $item['sizes'])->get();
            
            // foreach($currentSizesIds as $index => $size) {
            //     $affected = DB::table('clothes_size')
            //         ->where('clothes_id', '=', $clothes->id)
            //         ->where('size_id', '=', $size->size_id)
            //         ->update(['size_id', $newSizesObjectIds[$index]->id]);
            // }
            
            $clothes->update();
            return response()->json(['message' => 'updated']);
        } catch(\Exception) {
            return back()->withInput()->withErrors(
                ['default' =>
                'Something went wrong']);
        }
    }
    
    public function destroy(Clothes $clothes)
    {
        try {
            DB::table('clothes_size')->where('clothes_id', '=', $clothes->id)->delete();
            $clothes->delete();    
            return response()->json(['message' => 'deleted']);
            
        } catch(\Exception $e) {
            return back()->withErrors(
                ['default' => 'Error when deleting']);
        }
    }
    
    public function show(Clothes $clothes) {
        return view('shop.show', ['clothes' => $clothes]);
    }
}

<?php 
define('EXAMPLE_DIR', __DIR__);
require __DIR__ . '/../example_base.php';

use PHPR\Context;
use PHPR\Shader\Shader;
use PHPR\Mesh\{Vertex, Vertex\VertexPNU};
use PHPR\Mesh\VertexArray;
use PHPR\Math\{Vec3, Vec4, Mat4, Angle};

/**
 * Simpel cube shader
 */
class CubeShader extends Shader
{
    public Mat4 $mvp;

    /**
     * Vertex shader like thing
     */
    public function vertex(Vertex $vertex, array &$out) : Vec4
    {
        $out['color'] = $vertex->normal;
        
        return $this->mvp->multiplyVec3($vertex->position);
    }

    /**
     * Fragment shader like thing
     */
    public function fragment(array &$in, array &$out)
    {
        $out['color'] = $in['color']->normalize()->toColorInt();
    }
}

/**
 * Create shader object
 */
$shader = new CubeShader;

/**
 * Build a model view projection
 */
$projection = Mat4::perspective(Angle::degreesToRadians(45.0), EXAMPLE_RENDER_ASPECT_RATIO, 0.1, 100);

$view = (new Mat4())->inverse();

$model = (new Mat4)->translate(new Vec3(0.0, 0.0, -3));
$model->rotateX(0.45);
$model->rotateY(-0.45);

// multiply them togethrer
$shader->mvp = $model->multiply($view->multiply($projection, true), true);

/**
 * Build the context
 */
$context = create_exmaple_context();
$context->bindShader($shader);
$context->enableDepthTesting();
// $context->setDrawMode(Context::DRAW_MODE_LINES);

/**
 * Define the cube
 */
$cube = new VertexArray(VertexPNU::class, [
    // position        // normal        // uv
     0.5, -0.5, -0.5,  0.0,  0.0, -1.0, 1.0, 0.0,
    -0.5, -0.5, -0.5,  0.0,  0.0, -1.0, 0.0, 0.0,
     0.5,  0.5, -0.5,  0.0,  0.0, -1.0, 1.0, 1.0,
    -0.5,  0.5, -0.5,  0.0,  0.0, -1.0, 0.0, 1.0,
     0.5,  0.5, -0.5,  0.0,  0.0, -1.0, 1.0, 1.0,
    -0.5, -0.5, -0.5,  0.0,  0.0, -1.0, 0.0, 0.0,
    //
    -0.5, -0.5,  0.5,  0.0,  0.0,  1.0, 0.0, 0.0,
     0.5, -0.5,  0.5,  0.0,  0.0,  1.0, 1.0, 0.0,
     0.5,  0.5,  0.5,  0.0,  0.0,  1.0, 1.0, 1.0,
     0.5,  0.5,  0.5,  0.0,  0.0,  1.0, 1.0, 1.0,
    -0.5,  0.5,  0.5,  0.0,  0.0,  1.0, 0.0, 1.0,
    -0.5, -0.5,  0.5,  0.0,  0.0,  1.0, 0.0, 0.0,
    //
    -0.5,  0.5,  0.5, -1.0,  0.0,  0.0, 1.0, 0.0,
    -0.5,  0.5, -0.5, -1.0,  0.0,  0.0, 1.0, 1.0,
    -0.5, -0.5, -0.5, -1.0,  0.0,  0.0, 0.0, 1.0,
    -0.5, -0.5, -0.5, -1.0,  0.0,  0.0, 0.0, 1.0,
    -0.5, -0.5,  0.5, -1.0,  0.0,  0.0, 0.0, 0.0,
    -0.5,  0.5,  0.5, -1.0,  0.0,  0.0, 1.0, 0.0,
    //
     0.5,  0.5, -0.5,  1.0,  0.0,  0.0, 1.0, 1.0,
     0.5,  0.5,  0.5,  1.0,  0.0,  0.0, 1.0, 0.0,
     0.5, -0.5, -0.5,  1.0,  0.0,  0.0, 0.0, 1.0,
     0.5, -0.5,  0.5,  1.0,  0.0,  0.0, 0.0, 0.0,
     0.5, -0.5, -0.5,  1.0,  0.0,  0.0, 0.0, 1.0,
     0.5,  0.5,  0.5,  1.0,  0.0,  0.0, 1.0, 0.0,
    //
    -0.5, -0.5, -0.5,  0.0, -1.0,  0.0, 0.0, 1.0,
     0.5, -0.5, -0.5,  0.0, -1.0,  0.0, 1.0, 1.0,
     0.5, -0.5,  0.5,  0.0, -1.0,  0.0, 1.0, 0.0,
     0.5, -0.5,  0.5,  0.0, -1.0,  0.0, 1.0, 0.0,
    -0.5, -0.5,  0.5,  0.0, -1.0,  0.0, 0.0, 0.0,
    -0.5, -0.5, -0.5,  0.0, -1.0,  0.0, 0.0, 1.0,

     0.5,  0.5, -0.5,  0.0,  1.0,  0.0, 1.0, 1.0,
    -0.5,  0.5, -0.5,  0.0,  1.0,  0.0, 0.0, 1.0,
     0.5,  0.5,  0.5,  0.0,  1.0,  0.0, 1.0, 0.0,
    -0.5,  0.5,  0.5,  0.0,  1.0,  0.0, 0.0, 0.0,
     0.5,  0.5,  0.5,  0.0,  1.0,  0.0, 1.0, 0.0,
    -0.5,  0.5, -0.5,  0.0,  1.0,  0.0, 0.0, 1.0,
]);


/**
 * draw the cube
 */
$context->drawVertexArray($cube);


render_example_context($context);
render_example_context_depth($context);

<?php $root=""; ?>
<?php require($root."navigation.php"); ?>
<html>
<head>
  <?php load_style($root); ?>
</head>
 
<body>
 
<?php make_navigation("subdomains_ex2",$root)?>
 
<div class="content">
<a name="comments"></a> 
<div class = "comment">
<h1>Subdomains Example 2 - Subdomain-Restricted Variables</h1>

<br><br>This example builds on the fourth example program by showing how
to restrict solution fields to a subdomain (or union of
subdomains).


<br><br>

<br><br>C++ include files that we need
</div>

<div class ="fragment">
<pre>
        #include &lt;iostream&gt;
        #include &lt;algorithm&gt;
        #include &lt;math.h&gt;
        
</pre>
</div>
<div class = "comment">
Basic include file needed for the mesh functionality.
</div>

<div class ="fragment">
<pre>
        #include "libmesh.h"
        #include "mesh.h"
        #include "mesh_generation.h"
        #include "exodusII_io.h"
        #include "gnuplot_io.h"
        #include "linear_implicit_system.h"
        #include "equation_systems.h"
        
</pre>
</div>
<div class = "comment">
Define the Finite Element object.
</div>

<div class ="fragment">
<pre>
        #include "fe.h"
        
</pre>
</div>
<div class = "comment">
Define Gauss quadrature rules.
</div>

<div class ="fragment">
<pre>
        #include "quadrature_gauss.h"
        
</pre>
</div>
<div class = "comment">
Define the DofMap, which handles degree of freedom
indexing.
</div>

<div class ="fragment">
<pre>
        #include "dof_map.h"
        
</pre>
</div>
<div class = "comment">
Define useful datatypes for finite element
matrix and vector components.
</div>

<div class ="fragment">
<pre>
        #include "sparse_matrix.h"
        #include "numeric_vector.h"
        #include "dense_matrix.h"
        #include "dense_vector.h"
        
</pre>
</div>
<div class = "comment">
Define the PerfLog, a performance logging utility.
It is useful for timing events in a code and giving
you an idea where bottlenecks lie.
</div>

<div class ="fragment">
<pre>
        #include "perf_log.h"
        
</pre>
</div>
<div class = "comment">
The definition of a geometric element
</div>

<div class ="fragment">
<pre>
        #include "elem.h"
        
        #include "string_to_enum.h"
        #include "getpot.h"
        
</pre>
</div>
<div class = "comment">
Bring in everything from the libMesh namespace
</div>

<div class ="fragment">
<pre>
        using namespace libMesh;
         
        
        
</pre>
</div>
<div class = "comment">
Function prototype.  This is the function that will assemble
the linear system for our Poisson problem.  Note that the
function will take the \p EquationSystems object and the
name of the system we are assembling as input.  From the
\p EquationSystems object we have acess to the \p Mesh and
other objects we might need.
</div>

<div class ="fragment">
<pre>
        void assemble_poisson(EquationSystems& es,
                              const std::string& system_name);
        
</pre>
</div>
<div class = "comment">
Exact solution function prototype.
</div>

<div class ="fragment">
<pre>
        Real exact_solution (const Real x,
                             const Real y = 0.,
                             const Real z = 0.);
        
</pre>
</div>
<div class = "comment">
Begin the main program.
</div>

<div class ="fragment">
<pre>
        int main (int argc, char** argv)
        {
</pre>
</div>
<div class = "comment">
Initialize libMesh and any dependent libaries, like in example 2.
</div>

<div class ="fragment">
<pre>
          LibMeshInit init (argc, argv);
        
</pre>
</div>
<div class = "comment">
Declare a performance log for the main program
PerfLog perf_main("Main Program");
  

<br><br>Create a GetPot object to parse the command line
</div>

<div class ="fragment">
<pre>
          GetPot command_line (argc, argv);
          
</pre>
</div>
<div class = "comment">
Check for proper calling arguments.
</div>

<div class ="fragment">
<pre>
          if (argc &lt; 3)
            {
              if (libMesh::processor_id() == 0)
                std::cerr &lt;&lt; "Usage:\n"
                          &lt;&lt;"\t " &lt;&lt; argv[0] &lt;&lt; " -d 2(3)" &lt;&lt; " -n 15"
                          &lt;&lt; std::endl;
        
</pre>
</div>
<div class = "comment">
This handy function will print the file name, line number,
and then abort.  Currrently the library does not use C++
exception handling.
</div>

<div class ="fragment">
<pre>
              libmesh_error();
            }
          
</pre>
</div>
<div class = "comment">
Brief message to the user regarding the program name
and command line arguments.
</div>

<div class ="fragment">
<pre>
          else 
            {
              std::cout &lt;&lt; "Running " &lt;&lt; argv[0];
              
              for (int i=1; i&lt;argc; i++)
                std::cout &lt;&lt; " " &lt;&lt; argv[i];
              
              std::cout &lt;&lt; std::endl &lt;&lt; std::endl;
            }
          
        
</pre>
</div>
<div class = "comment">
Read problem dimension from command line.  Use int
instead of unsigned since the GetPot overload is ambiguous
otherwise.
</div>

<div class ="fragment">
<pre>
          int dim = 2;
          if ( command_line.search(1, "-d") )
            dim = command_line.next(dim);
          
</pre>
</div>
<div class = "comment">
Skip higher-dimensional examples on a lower-dimensional libMesh build
</div>

<div class ="fragment">
<pre>
          libmesh_example_assert(dim &lt;= LIBMESH_DIM, "2D/3D support");
            
</pre>
</div>
<div class = "comment">
Create a mesh with user-defined dimension.
</div>

<div class ="fragment">
<pre>
          Mesh mesh (dim);
          
</pre>
</div>
<div class = "comment">
Read number of elements from command line
</div>

<div class ="fragment">
<pre>
          int ps = 15;
          if ( command_line.search(1, "-n") )
            ps = command_line.next(ps);
          
</pre>
</div>
<div class = "comment">
Read FE order from command line
</div>

<div class ="fragment">
<pre>
          std::string order = "SECOND"; 
          if ( command_line.search(2, "-Order", "-o") )
            order = command_line.next(order);
        
</pre>
</div>
<div class = "comment">
Read FE Family from command line
</div>

<div class ="fragment">
<pre>
          std::string family = "LAGRANGE"; 
          if ( command_line.search(2, "-FEFamily", "-f") )
            family = command_line.next(family);
          
</pre>
</div>
<div class = "comment">
Cannot use discontinuous basis.
</div>

<div class ="fragment">
<pre>
          if ((family == "MONOMIAL") || (family == "XYZ"))
            {
              if (libMesh::processor_id() == 0)
                std::cerr &lt;&lt; "ex28 currently requires a C^0 (or higher) FE basis." &lt;&lt; std::endl;
              libmesh_error();
            }
        
</pre>
</div>
<div class = "comment">
Use the MeshTools::Generation mesh generator to create a uniform
grid on the square [-1,1]^D.  We instruct the mesh generator
to build a mesh of 8x8 \p Quad9 elements in 2D, or \p Hex27
elements in 3D.  Building these higher-order elements allows
us to use higher-order approximation, as in example 3.


<br><br></div>

<div class ="fragment">
<pre>
          Real halfwidth = dim &gt; 1 ? 1. : 0.;
          Real halfheight = dim &gt; 2 ? 1. : 0.;
        
          if ((family == "LAGRANGE") && (order == "FIRST"))
            {
</pre>
</div>
<div class = "comment">
No reason to use high-order geometric elements if we are
solving with low-order finite elements.
</div>

<div class ="fragment">
<pre>
              MeshTools::Generation::build_cube (mesh,
                                                 ps,
        					 (dim&gt;1) ? ps : 0,
        					 (dim&gt;2) ? ps : 0,
                                                 -1., 1.,
                                                 -halfwidth, halfwidth,
                                                 -halfheight, halfheight,
                                                 (dim==1)    ? EDGE2 : 
                                                 ((dim == 2) ? QUAD4 : HEX8));
            }
          
          else
            {
              MeshTools::Generation::build_cube (mesh,
        					 ps,
        					 (dim&gt;1) ? ps : 0,
        					 (dim&gt;2) ? ps : 0,
                                                 -1., 1.,
                                                 -halfwidth, halfwidth,
                                                 -halfheight, halfheight,
                                                 (dim==1)    ? EDGE3 : 
                                                 ((dim == 2) ? QUAD9 : HEX27));
            }
        
          {
            MeshBase::element_iterator       el     = mesh.elements_begin();
            const MeshBase::element_iterator end_el = mesh.elements_end();
            
            for ( ; el != end_el; ++el)
              {
        	Elem* elem = *el;
        	const Point cent = elem-&gt;centroid();
                if (dim &gt; 1)
                  {
        	    if ((cent(0) &gt; 0) == (cent(1) &gt; 0))
        	      elem-&gt;subdomain_id() = 1;	
                  }
                else
                  {
        	    if (cent(0) &gt; 0)
        	      elem-&gt;subdomain_id() = 1;	
                  }
              }
          }
        
</pre>
</div>
<div class = "comment">
Print information about the mesh to the screen.
</div>

<div class ="fragment">
<pre>
          mesh.print_info();
            
</pre>
</div>
<div class = "comment">
Create an equation systems object.
</div>

<div class ="fragment">
<pre>
          EquationSystems equation_systems (mesh);
          
</pre>
</div>
<div class = "comment">
Declare the system and its variables.
Create a system named "Poisson"
</div>

<div class ="fragment">
<pre>
          LinearImplicitSystem& system =
            equation_systems.add_system&lt;LinearImplicitSystem&gt; ("Poisson");
        
          
          std::set&lt;subdomain_id_type&gt; active_subdomains;
        
          
</pre>
</div>
<div class = "comment">
Add the variable "u" to "Poisson".  "u"
will be approximated using second-order approximation.
</div>

<div class ="fragment">
<pre>
          active_subdomains.clear(); active_subdomains.insert(0);
          system.add_variable("u",
                              Utility::string_to_enum&lt;Order&gt;   (order),
                              Utility::string_to_enum&lt;FEFamily&gt;(family),
        		      &active_subdomains);
        
</pre>
</div>
<div class = "comment">
Add the variable "v" to "Poisson".  "v"
will be approximated using second-order approximation.
</div>

<div class ="fragment">
<pre>
          active_subdomains.clear(); active_subdomains.insert(1);
          system.add_variable("v",
                              Utility::string_to_enum&lt;Order&gt;   (order),
                              Utility::string_to_enum&lt;FEFamily&gt;(family),
        		      &active_subdomains);
        
</pre>
</div>
<div class = "comment">
Give the system a pointer to the matrix assembly
function.
</div>

<div class ="fragment">
<pre>
          system.attach_assemble_function (assemble_poisson);
          
</pre>
</div>
<div class = "comment">
Initialize the data structures for the equation system.
</div>

<div class ="fragment">
<pre>
          equation_systems.init();
        
</pre>
</div>
<div class = "comment">
Print information about the system to the screen.
</div>

<div class ="fragment">
<pre>
          equation_systems.print_info();
          mesh.print_info();
        
</pre>
</div>
<div class = "comment">
Solve the system "Poisson", just like example 2.
</div>

<div class ="fragment">
<pre>
          equation_systems.get_system("Poisson").solve();
        
</pre>
</div>
<div class = "comment">
After solving the system write the solution
to a GMV-formatted plot file.
</div>

<div class ="fragment">
<pre>
          if(dim == 1)
          {        
            GnuPlotIO plot(mesh,"Subdomains Example 2, 1D",GnuPlotIO::GRID_ON);
            plot.write_equation_systems("gnuplot_script",equation_systems);
          }
          else
          {
        #ifdef LIBMESH_HAVE_EXODUS_API
            ExodusII_IO (mesh).write_equation_systems ((dim == 3) ? 
              "out_3.e" : "out_2.e",equation_systems);
        #endif // #ifdef LIBMESH_HAVE_EXODUS_API
          }
          
</pre>
</div>
<div class = "comment">
All done.  
</div>

<div class ="fragment">
<pre>
          return 0;
        }
        
        
        
        
</pre>
</div>
<div class = "comment">

<br><br>
<br><br>
<br><br>We now define the matrix assembly function for the
Poisson system.  We need to first compute element
matrices and right-hand sides, and then take into
account the boundary conditions, which will be handled
via a penalty method.
</div>

<div class ="fragment">
<pre>
        void assemble_poisson(EquationSystems& es,
                              const std::string& system_name)
        {
</pre>
</div>
<div class = "comment">
It is a good idea to make sure we are assembling
the proper system.
</div>

<div class ="fragment">
<pre>
          libmesh_assert (system_name == "Poisson");
        
</pre>
</div>
<div class = "comment">
Declare a performance log.  Give it a descriptive
string to identify what part of the code we are
logging, since there may be many PerfLogs in an
application.
</div>

<div class ="fragment">
<pre>
          PerfLog perf_log ("Matrix Assembly");
          
</pre>
</div>
<div class = "comment">
Get a constant reference to the mesh object.
</div>

<div class ="fragment">
<pre>
          const MeshBase& mesh = es.get_mesh();
        
</pre>
</div>
<div class = "comment">
The dimension that we are running
</div>

<div class ="fragment">
<pre>
          const unsigned int dim = mesh.mesh_dimension();
        
</pre>
</div>
<div class = "comment">
Get a reference to the LinearImplicitSystem we are solving
</div>

<div class ="fragment">
<pre>
          LinearImplicitSystem& system = es.get_system&lt;LinearImplicitSystem&gt;("Poisson");
          
</pre>
</div>
<div class = "comment">
A reference to the \p DofMap object for this system.  The \p DofMap
object handles the index translation from node and element numbers
to degree of freedom numbers.  We will talk more about the \p DofMap
in future examples.
</div>

<div class ="fragment">
<pre>
          const DofMap& dof_map = system.get_dof_map();
        
</pre>
</div>
<div class = "comment">
Get a constant reference to the Finite Element type
for the first (and only) variable in the system.
</div>

<div class ="fragment">
<pre>
          FEType fe_type = dof_map.variable_type(0);
        
</pre>
</div>
<div class = "comment">
Build a Finite Element object of the specified type.  Since the
\p FEBase::build() member dynamically creates memory we will
store the object as an \p AutoPtr<FEBase>.  This can be thought
of as a pointer that will clean up after itself.
</div>

<div class ="fragment">
<pre>
          AutoPtr&lt;FEBase&gt; fe (FEBase::build(dim, fe_type));
          
</pre>
</div>
<div class = "comment">
A 5th order Gauss quadrature rule for numerical integration.
</div>

<div class ="fragment">
<pre>
          QGauss qrule (dim, FIFTH);
        
</pre>
</div>
<div class = "comment">
Tell the finite element object to use our quadrature rule.
</div>

<div class ="fragment">
<pre>
          fe-&gt;attach_quadrature_rule (&qrule);
        
</pre>
</div>
<div class = "comment">
Declare a special finite element object for
boundary integration.
</div>

<div class ="fragment">
<pre>
          AutoPtr&lt;FEBase&gt; fe_face (FEBase::build(dim, fe_type));
                      
</pre>
</div>
<div class = "comment">
Boundary integration requires one quadraure rule,
with dimensionality one less than the dimensionality
of the element.
</div>

<div class ="fragment">
<pre>
          QGauss qface(dim-1, FIFTH);
          
</pre>
</div>
<div class = "comment">
Tell the finte element object to use our
quadrature rule.
</div>

<div class ="fragment">
<pre>
          fe_face-&gt;attach_quadrature_rule (&qface);
        
</pre>
</div>
<div class = "comment">
Here we define some references to cell-specific data that
will be used to assemble the linear system.
We begin with the element Jacobian * quadrature weight at each
integration point.   
</div>

<div class ="fragment">
<pre>
          const std::vector&lt;Real&gt;& JxW = fe-&gt;get_JxW();
        
</pre>
</div>
<div class = "comment">
The physical XY locations of the quadrature points on the element.
These might be useful for evaluating spatially varying material
properties at the quadrature points.
</div>

<div class ="fragment">
<pre>
          const std::vector&lt;Point&gt;& q_point = fe-&gt;get_xyz();
        
</pre>
</div>
<div class = "comment">
The element shape functions evaluated at the quadrature points.
</div>

<div class ="fragment">
<pre>
          const std::vector&lt;std::vector&lt;Real&gt; &gt;& phi = fe-&gt;get_phi();
        
</pre>
</div>
<div class = "comment">
The element shape function gradients evaluated at the quadrature
points.
</div>

<div class ="fragment">
<pre>
          const std::vector&lt;std::vector&lt;RealGradient&gt; &gt;& dphi = fe-&gt;get_dphi();
        
</pre>
</div>
<div class = "comment">
Define data structures to contain the element matrix
and right-hand-side vector contribution.  Following
basic finite element terminology we will denote these
"Ke" and "Fe". More detail is in example 3.
</div>

<div class ="fragment">
<pre>
          DenseMatrix&lt;Number&gt; Ke;
          DenseVector&lt;Number&gt; Fe;
        
</pre>
</div>
<div class = "comment">
This vector will hold the degree of freedom indices for
the element.  These define where in the global system
the element degrees of freedom get mapped.
</div>

<div class ="fragment">
<pre>
          std::vector&lt;unsigned int&gt; dof_indices, dof_indices2;
        
</pre>
</div>
<div class = "comment">
Now we will loop over all the elements in the mesh.
We will compute the element matrix and right-hand-side
contribution.  See example 3 for a discussion of the
element iterators.  Here we use the \p const_local_elem_iterator
to indicate we only want to loop over elements that are assigned
to the local processor.  This allows each processor to compute
its components of the global matrix.

<br><br>"PARALLEL CHANGE"
</div>

<div class ="fragment">
<pre>
          MeshBase::const_element_iterator       el     = mesh.local_elements_begin();
          const MeshBase::const_element_iterator end_el = mesh.local_elements_end();
        
          for ( ; el != end_el; ++el)
            {
</pre>
</div>
<div class = "comment">
Start logging the shape function initialization.
This is done through a simple function call with
the name of the event to log.
</div>

<div class ="fragment">
<pre>
              perf_log.push("elem init");      
        
</pre>
</div>
<div class = "comment">
Store a pointer to the element we are currently
working on.  This allows for nicer syntax later.
</div>

<div class ="fragment">
<pre>
              const Elem* elem = *el;
        
</pre>
</div>
<div class = "comment">
Get the degree of freedom indices for the
current element.  These define where in the global
matrix and right-hand-side this element will
contribute to.
</div>

<div class ="fragment">
<pre>
              dof_map.dof_indices (elem, dof_indices,0);
              dof_map.dof_indices (elem, dof_indices2,1);
        
</pre>
</div>
<div class = "comment">
std::cout << "dof_indices.size()="
<< dof_indices.size() 
<< ", dof_indices2.size()="
<< dof_indices2.size()
<< std::endl;


<br><br>Compute the element-specific data for the current
element.  This involves computing the location of the
quadrature points (q_point) and the shape functions
(phi, dphi) for the current element.
</div>

<div class ="fragment">
<pre>
              fe-&gt;reinit (elem);
        
</pre>
</div>
<div class = "comment">
Zero the element matrix and right-hand side before
summing them.  We use the resize member here because
the number of degrees of freedom might have changed from
the last element.  Note that this will be the case if the
element type is different (i.e. the last element was a
triangle, now we are on a quadrilateral).
</div>

<div class ="fragment">
<pre>
              Ke.resize (std::max(dof_indices.size(), dof_indices2.size()),
        		 std::max(dof_indices.size(), dof_indices2.size()));
        
              Fe.resize (std::max(dof_indices.size(), dof_indices2.size()));
        
</pre>
</div>
<div class = "comment">
Stop logging the shape function initialization.
If you forget to stop logging an event the PerfLog
object will probably catch the error and abort.
</div>

<div class ="fragment">
<pre>
              perf_log.pop("elem init");      
        
</pre>
</div>
<div class = "comment">
Now we will build the element matrix.  This involves
a double loop to integrate the test funcions (i) against
the trial functions (j).

<br><br>We have split the numeric integration into two loops
so that we can log the matrix and right-hand-side
computation seperately.

<br><br>Now start logging the element matrix computation
</div>

<div class ="fragment">
<pre>
              perf_log.push ("Ke");
        
              for (unsigned int qp=0; qp&lt;qrule.n_points(); qp++)
                for (unsigned int i=0; i&lt;phi.size(); i++)
                  for (unsigned int j=0; j&lt;phi.size(); j++)
                    Ke(i,j) += JxW[qp]*(dphi[i][qp]*dphi[j][qp]);
                    
        
</pre>
</div>
<div class = "comment">
Stop logging the matrix computation
</div>

<div class ="fragment">
<pre>
              perf_log.pop ("Ke");
        
</pre>
</div>
<div class = "comment">
Now we build the element right-hand-side contribution.
This involves a single loop in which we integrate the
"forcing function" in the PDE against the test functions.

<br><br>Start logging the right-hand-side computation
</div>

<div class ="fragment">
<pre>
              perf_log.push ("Fe");
              
              for (unsigned int qp=0; qp&lt;qrule.n_points(); qp++)
                {
</pre>
</div>
<div class = "comment">
fxy is the forcing function for the Poisson equation.
In this case we set fxy to be a finite difference
Laplacian approximation to the (known) exact solution.

<br><br>We will use the second-order accurate FD Laplacian
approximation, which in 2D on a structured grid is

<br><br>u_xx + u_yy = (u(i-1,j) + u(i+1,j) +
u(i,j-1) + u(i,j+1) +
-4*u(i,j))/h^2

<br><br>Since the value of the forcing function depends only
on the location of the quadrature point (q_point[qp])
we will compute it here, outside of the i-loop          
</div>

<div class ="fragment">
<pre>
                  const Real x = q_point[qp](0);
        #if LIBMESH_DIM &gt; 1
                  const Real y = q_point[qp](1);
        #else
                  const Real y = 0;
        #endif
        #if LIBMESH_DIM &gt; 2
                  const Real z = q_point[qp](2);
        #else
                  const Real z = 0;
        #endif
                  const Real eps = 1.e-3;
        
                  const Real uxx = (exact_solution(x-eps,y,z) +
                                    exact_solution(x+eps,y,z) +
                                    -2.*exact_solution(x,y,z))/eps/eps;
                      
                  const Real uyy = (exact_solution(x,y-eps,z) +
                                    exact_solution(x,y+eps,z) +
                                    -2.*exact_solution(x,y,z))/eps/eps;
                  
                  const Real uzz = (exact_solution(x,y,z-eps) +
                                    exact_solution(x,y,z+eps) +
                                    -2.*exact_solution(x,y,z))/eps/eps;
        
                  Real fxy;
                  if(dim==1)
                  {
</pre>
</div>
<div class = "comment">
In 1D, compute the rhs by differentiating the
exact solution twice.
</div>

<div class ="fragment">
<pre>
                    const Real pi = libMesh::pi;
                    fxy = (0.25*pi*pi)*sin(.5*pi*x);
                  }
                  else
                  {
                    fxy = - (uxx + uyy + ((dim==2) ? 0. : uzz));
                  } 
        
</pre>
</div>
<div class = "comment">
Add the RHS contribution
</div>

<div class ="fragment">
<pre>
                  for (unsigned int i=0; i&lt;phi.size(); i++)
                    Fe(i) += JxW[qp]*fxy*phi[i][qp];          
                }
              
</pre>
</div>
<div class = "comment">
Stop logging the right-hand-side computation
</div>

<div class ="fragment">
<pre>
              perf_log.pop ("Fe");
        
</pre>
</div>
<div class = "comment">
At this point the interior element integration has
been completed.  However, we have not yet addressed
boundary conditions.  For this example we will only
consider simple Dirichlet boundary conditions imposed
via the penalty method. This is discussed at length in
example 3.
</div>

<div class ="fragment">
<pre>
              {
                
</pre>
</div>
<div class = "comment">
Start logging the boundary condition computation
</div>

<div class ="fragment">
<pre>
                perf_log.push ("BCs");
        
</pre>
</div>
<div class = "comment">
The following loops over the sides of the element.
If the element has no neighbor on a side then that
side MUST live on a boundary of the domain.
</div>

<div class ="fragment">
<pre>
                for (unsigned int side=0; side&lt;elem-&gt;n_sides(); side++)
                  if ((elem-&gt;neighbor(side) == NULL) ||
        	      (elem-&gt;neighbor(side)-&gt;subdomain_id() != elem-&gt;subdomain_id()))
                    {
                    
</pre>
</div>
<div class = "comment">
The penalty value.  \frac{1}{\epsilon}
in the discussion above.
</div>

<div class ="fragment">
<pre>
                      const Real penalty = 1.e10;
        
</pre>
</div>
<div class = "comment">
The value of the shape functions at the quadrature
points.
</div>

<div class ="fragment">
<pre>
                      const std::vector&lt;std::vector&lt;Real&gt; &gt;&  phi_face = fe_face-&gt;get_phi();
        
</pre>
</div>
<div class = "comment">
The Jacobian * Quadrature Weight at the quadrature
points on the face.
</div>

<div class ="fragment">
<pre>
                      const std::vector&lt;Real&gt;& JxW_face = fe_face-&gt;get_JxW();
        
</pre>
</div>
<div class = "comment">
The XYZ locations (in physical space) of the
quadrature points on the face.  This is where
we will interpolate the boundary value function.
</div>

<div class ="fragment">
<pre>
                      const std::vector&lt;Point &gt;& qface_point = fe_face-&gt;get_xyz();
        
</pre>
</div>
<div class = "comment">
Compute the shape function values on the element
face.
</div>

<div class ="fragment">
<pre>
                      fe_face-&gt;reinit(elem, side);
        
</pre>
</div>
<div class = "comment">
Loop over the face quadrature points for integration.
</div>

<div class ="fragment">
<pre>
                      for (unsigned int qp=0; qp&lt;qface.n_points(); qp++)
                      {
</pre>
</div>
<div class = "comment">
The location on the boundary of the current
face quadrature point.
</div>

<div class ="fragment">
<pre>
                        const Real xf = qface_point[qp](0);
        #if LIBMESH_DIM &gt; 1
                        const Real yf = qface_point[qp](1);
        #else
                        const Real yf = 0.;
        #endif
        #if LIBMESH_DIM &gt; 2
                        const Real zf = qface_point[qp](2);
        #else
                        const Real zf = 0.;
        #endif
        
        
</pre>
</div>
<div class = "comment">
The boundary value.
</div>

<div class ="fragment">
<pre>
                        const Real value = exact_solution(xf, yf, zf);
        
</pre>
</div>
<div class = "comment">
Matrix contribution of the L2 projection. 
</div>

<div class ="fragment">
<pre>
                        for (unsigned int i=0; i&lt;phi_face.size(); i++)
                          for (unsigned int j=0; j&lt;phi_face.size(); j++)
                            Ke(i,j) += JxW_face[qp]*penalty*phi_face[i][qp]*phi_face[j][qp];
        
</pre>
</div>
<div class = "comment">
Right-hand-side contribution of the L2
projection.
</div>

<div class ="fragment">
<pre>
                        for (unsigned int i=0; i&lt;phi_face.size(); i++)
                          Fe(i) += JxW_face[qp]*penalty*value*phi_face[i][qp];
                      } 
                    }
                    
                
</pre>
</div>
<div class = "comment">
Stop logging the boundary condition computation
</div>

<div class ="fragment">
<pre>
                perf_log.pop ("BCs");
              } 
              
        
</pre>
</div>
<div class = "comment">
The element matrix and right-hand-side are now built
for this element.  Add them to the global matrix and
right-hand-side vector.  The \p PetscMatrix::add_matrix()
and \p PetscVector::add_vector() members do this for us.
Start logging the insertion of the local (element)
matrix and vector into the global matrix and vector
</div>

<div class ="fragment">
<pre>
              perf_log.push ("matrix insertion");
              
              if (dof_indices.size())
        	{
        	  system.matrix-&gt;add_matrix (Ke, dof_indices);
        	  system.rhs-&gt;add_vector    (Fe, dof_indices);
        	}
              
              if (dof_indices2.size())
        	{
        	  system.matrix-&gt;add_matrix (Ke, dof_indices2);
        	  system.rhs-&gt;add_vector    (Fe, dof_indices2);
        	}
        
</pre>
</div>
<div class = "comment">
Start logging the insertion of the local (element)
matrix and vector into the global matrix and vector
</div>

<div class ="fragment">
<pre>
              perf_log.pop ("matrix insertion");
            }
        
</pre>
</div>
<div class = "comment">
That's it.  We don't need to do anything else to the
PerfLog.  When it goes out of scope (at this function return)
it will print its log to the screen. Pretty easy, huh?
</div>

<div class ="fragment">
<pre>
        }
</pre>
</div>

<a name="nocomments"></a> 
<br><br><br> <h1> The program without comments: </h1> 
<pre> 
  
  
  #include &lt;iostream&gt;
  #include &lt;algorithm&gt;
  #include &lt;math.h&gt;
  
  #include <B><FONT COLOR="#BC8F8F">&quot;libmesh.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;mesh.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;mesh_generation.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;exodusII_io.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;gnuplot_io.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;linear_implicit_system.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;equation_systems.h&quot;</FONT></B>
  
  #include <B><FONT COLOR="#BC8F8F">&quot;fe.h&quot;</FONT></B>
  
  #include <B><FONT COLOR="#BC8F8F">&quot;quadrature_gauss.h&quot;</FONT></B>
  
  #include <B><FONT COLOR="#BC8F8F">&quot;dof_map.h&quot;</FONT></B>
  
  #include <B><FONT COLOR="#BC8F8F">&quot;sparse_matrix.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;numeric_vector.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;dense_matrix.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;dense_vector.h&quot;</FONT></B>
  
  #include <B><FONT COLOR="#BC8F8F">&quot;perf_log.h&quot;</FONT></B>
  
  #include <B><FONT COLOR="#BC8F8F">&quot;elem.h&quot;</FONT></B>
  
  #include <B><FONT COLOR="#BC8F8F">&quot;string_to_enum.h&quot;</FONT></B>
  #include <B><FONT COLOR="#BC8F8F">&quot;getpot.h&quot;</FONT></B>
  
  using namespace libMesh;
   
  
  
  <B><FONT COLOR="#228B22">void</FONT></B> assemble_poisson(EquationSystems&amp; es,
                        <B><FONT COLOR="#228B22">const</FONT></B> std::string&amp; system_name);
  
  Real exact_solution (<B><FONT COLOR="#228B22">const</FONT></B> Real x,
                       <B><FONT COLOR="#228B22">const</FONT></B> Real y = 0.,
                       <B><FONT COLOR="#228B22">const</FONT></B> Real z = 0.);
  
  <B><FONT COLOR="#228B22">int</FONT></B> main (<B><FONT COLOR="#228B22">int</FONT></B> argc, <B><FONT COLOR="#228B22">char</FONT></B>** argv)
  {
    LibMeshInit init (argc, argv);
  
    
    GetPot command_line (argc, argv);
    
    <B><FONT COLOR="#A020F0">if</FONT></B> (argc &lt; 3)
      {
        <B><FONT COLOR="#A020F0">if</FONT></B> (libMesh::processor_id() == 0)
          <B><FONT COLOR="#5F9EA0">std</FONT></B>::cerr &lt;&lt; <B><FONT COLOR="#BC8F8F">&quot;Usage:\n&quot;</FONT></B>
                    &lt;&lt;<B><FONT COLOR="#BC8F8F">&quot;\t &quot;</FONT></B> &lt;&lt; argv[0] &lt;&lt; <B><FONT COLOR="#BC8F8F">&quot; -d 2(3)&quot;</FONT></B> &lt;&lt; <B><FONT COLOR="#BC8F8F">&quot; -n 15&quot;</FONT></B>
                    &lt;&lt; std::endl;
  
        libmesh_error();
      }
    
    <B><FONT COLOR="#A020F0">else</FONT></B> 
      {
        <B><FONT COLOR="#5F9EA0">std</FONT></B>::cout &lt;&lt; <B><FONT COLOR="#BC8F8F">&quot;Running &quot;</FONT></B> &lt;&lt; argv[0];
        
        <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">int</FONT></B> i=1; i&lt;argc; i++)
          <B><FONT COLOR="#5F9EA0">std</FONT></B>::cout &lt;&lt; <B><FONT COLOR="#BC8F8F">&quot; &quot;</FONT></B> &lt;&lt; argv[i];
        
        <B><FONT COLOR="#5F9EA0">std</FONT></B>::cout &lt;&lt; std::endl &lt;&lt; std::endl;
      }
    
  
    <B><FONT COLOR="#228B22">int</FONT></B> dim = 2;
    <B><FONT COLOR="#A020F0">if</FONT></B> ( command_line.search(1, <B><FONT COLOR="#BC8F8F">&quot;-d&quot;</FONT></B>) )
      dim = command_line.next(dim);
    
    libmesh_example_assert(dim &lt;= LIBMESH_DIM, <B><FONT COLOR="#BC8F8F">&quot;2D/3D support&quot;</FONT></B>);
      
    Mesh mesh (dim);
    
    <B><FONT COLOR="#228B22">int</FONT></B> ps = 15;
    <B><FONT COLOR="#A020F0">if</FONT></B> ( command_line.search(1, <B><FONT COLOR="#BC8F8F">&quot;-n&quot;</FONT></B>) )
      ps = command_line.next(ps);
    
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::string order = <B><FONT COLOR="#BC8F8F">&quot;SECOND&quot;</FONT></B>; 
    <B><FONT COLOR="#A020F0">if</FONT></B> ( command_line.search(2, <B><FONT COLOR="#BC8F8F">&quot;-Order&quot;</FONT></B>, <B><FONT COLOR="#BC8F8F">&quot;-o&quot;</FONT></B>) )
      order = command_line.next(order);
  
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::string family = <B><FONT COLOR="#BC8F8F">&quot;LAGRANGE&quot;</FONT></B>; 
    <B><FONT COLOR="#A020F0">if</FONT></B> ( command_line.search(2, <B><FONT COLOR="#BC8F8F">&quot;-FEFamily&quot;</FONT></B>, <B><FONT COLOR="#BC8F8F">&quot;-f&quot;</FONT></B>) )
      family = command_line.next(family);
    
    <B><FONT COLOR="#A020F0">if</FONT></B> ((family == <B><FONT COLOR="#BC8F8F">&quot;MONOMIAL&quot;</FONT></B>) || (family == <B><FONT COLOR="#BC8F8F">&quot;XYZ&quot;</FONT></B>))
      {
        <B><FONT COLOR="#A020F0">if</FONT></B> (libMesh::processor_id() == 0)
          <B><FONT COLOR="#5F9EA0">std</FONT></B>::cerr &lt;&lt; <B><FONT COLOR="#BC8F8F">&quot;ex28 currently requires a C^0 (or higher) FE basis.&quot;</FONT></B> &lt;&lt; std::endl;
        libmesh_error();
      }
  
  
    Real halfwidth = dim &gt; 1 ? 1. : 0.;
    Real halfheight = dim &gt; 2 ? 1. : 0.;
  
    <B><FONT COLOR="#A020F0">if</FONT></B> ((family == <B><FONT COLOR="#BC8F8F">&quot;LAGRANGE&quot;</FONT></B>) &amp;&amp; (order == <B><FONT COLOR="#BC8F8F">&quot;FIRST&quot;</FONT></B>))
      {
        <B><FONT COLOR="#5F9EA0">MeshTools</FONT></B>::Generation::build_cube (mesh,
                                           ps,
  					 (dim&gt;1) ? ps : 0,
  					 (dim&gt;2) ? ps : 0,
                                           -1., 1.,
                                           -halfwidth, halfwidth,
                                           -halfheight, halfheight,
                                           (dim==1)    ? EDGE2 : 
                                           ((dim == 2) ? QUAD4 : HEX8));
      }
    
    <B><FONT COLOR="#A020F0">else</FONT></B>
      {
        <B><FONT COLOR="#5F9EA0">MeshTools</FONT></B>::Generation::build_cube (mesh,
  					 ps,
  					 (dim&gt;1) ? ps : 0,
  					 (dim&gt;2) ? ps : 0,
                                           -1., 1.,
                                           -halfwidth, halfwidth,
                                           -halfheight, halfheight,
                                           (dim==1)    ? EDGE3 : 
                                           ((dim == 2) ? QUAD9 : HEX27));
      }
  
    {
      <B><FONT COLOR="#5F9EA0">MeshBase</FONT></B>::element_iterator       el     = mesh.elements_begin();
      <B><FONT COLOR="#228B22">const</FONT></B> MeshBase::element_iterator end_el = mesh.elements_end();
      
      <B><FONT COLOR="#A020F0">for</FONT></B> ( ; el != end_el; ++el)
        {
  	Elem* elem = *el;
  	<B><FONT COLOR="#228B22">const</FONT></B> Point cent = elem-&gt;centroid();
          <B><FONT COLOR="#A020F0">if</FONT></B> (dim &gt; 1)
            {
  	    <B><FONT COLOR="#A020F0">if</FONT></B> ((cent(0) &gt; 0) == (cent(1) &gt; 0))
  	      elem-&gt;subdomain_id() = 1;	
            }
          <B><FONT COLOR="#A020F0">else</FONT></B>
            {
  	    <B><FONT COLOR="#A020F0">if</FONT></B> (cent(0) &gt; 0)
  	      elem-&gt;subdomain_id() = 1;	
            }
        }
    }
  
    mesh.print_info();
      
    EquationSystems equation_systems (mesh);
    
    LinearImplicitSystem&amp; system =
      equation_systems.add_system&lt;LinearImplicitSystem&gt; (<B><FONT COLOR="#BC8F8F">&quot;Poisson&quot;</FONT></B>);
  
    
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::set&lt;subdomain_id_type&gt; active_subdomains;
  
    
    active_subdomains.clear(); active_subdomains.insert(0);
    system.add_variable(<B><FONT COLOR="#BC8F8F">&quot;u&quot;</FONT></B>,
                        <B><FONT COLOR="#5F9EA0">Utility</FONT></B>::string_to_enum&lt;Order&gt;   (order),
                        <B><FONT COLOR="#5F9EA0">Utility</FONT></B>::string_to_enum&lt;FEFamily&gt;(family),
  		      &amp;active_subdomains);
  
    active_subdomains.clear(); active_subdomains.insert(1);
    system.add_variable(<B><FONT COLOR="#BC8F8F">&quot;v&quot;</FONT></B>,
                        <B><FONT COLOR="#5F9EA0">Utility</FONT></B>::string_to_enum&lt;Order&gt;   (order),
                        <B><FONT COLOR="#5F9EA0">Utility</FONT></B>::string_to_enum&lt;FEFamily&gt;(family),
  		      &amp;active_subdomains);
  
    system.attach_assemble_function (assemble_poisson);
    
    equation_systems.init();
  
    equation_systems.print_info();
    mesh.print_info();
  
    equation_systems.get_system(<B><FONT COLOR="#BC8F8F">&quot;Poisson&quot;</FONT></B>).solve();
  
    <B><FONT COLOR="#A020F0">if</FONT></B>(dim == 1)
    {        
      GnuPlotIO plot(mesh,<B><FONT COLOR="#BC8F8F">&quot;Subdomains Example 2, 1D&quot;</FONT></B>,GnuPlotIO::GRID_ON);
      plot.write_equation_systems(<B><FONT COLOR="#BC8F8F">&quot;gnuplot_script&quot;</FONT></B>,equation_systems);
    }
    <B><FONT COLOR="#A020F0">else</FONT></B>
    {
  #ifdef LIBMESH_HAVE_EXODUS_API
      ExodusII_IO (mesh).write_equation_systems ((dim == 3) ? 
        <B><FONT COLOR="#BC8F8F">&quot;out_3.e&quot;</FONT></B> : <B><FONT COLOR="#BC8F8F">&quot;out_2.e&quot;</FONT></B>,equation_systems);
  #endif <I><FONT COLOR="#B22222">// #ifdef LIBMESH_HAVE_EXODUS_API
</FONT></I>    }
    
    <B><FONT COLOR="#A020F0">return</FONT></B> 0;
  }
  
  
  
  
  <B><FONT COLOR="#228B22">void</FONT></B> assemble_poisson(EquationSystems&amp; es,
                        <B><FONT COLOR="#228B22">const</FONT></B> std::string&amp; system_name)
  {
    libmesh_assert (system_name == <B><FONT COLOR="#BC8F8F">&quot;Poisson&quot;</FONT></B>);
  
    PerfLog perf_log (<B><FONT COLOR="#BC8F8F">&quot;Matrix Assembly&quot;</FONT></B>);
    
    <B><FONT COLOR="#228B22">const</FONT></B> MeshBase&amp; mesh = es.get_mesh();
  
    <B><FONT COLOR="#228B22">const</FONT></B> <B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> dim = mesh.mesh_dimension();
  
    LinearImplicitSystem&amp; system = es.get_system&lt;LinearImplicitSystem&gt;(<B><FONT COLOR="#BC8F8F">&quot;Poisson&quot;</FONT></B>);
    
    <B><FONT COLOR="#228B22">const</FONT></B> DofMap&amp; dof_map = system.get_dof_map();
  
    FEType fe_type = dof_map.variable_type(0);
  
    AutoPtr&lt;FEBase&gt; fe (FEBase::build(dim, fe_type));
    
    QGauss qrule (dim, FIFTH);
  
    fe-&gt;attach_quadrature_rule (&amp;qrule);
  
    AutoPtr&lt;FEBase&gt; fe_face (FEBase::build(dim, fe_type));
                
    QGauss qface(dim-1, FIFTH);
    
    fe_face-&gt;attach_quadrature_rule (&amp;qface);
  
    <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;Real&gt;&amp; JxW = fe-&gt;get_JxW();
  
    <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;Point&gt;&amp; q_point = fe-&gt;get_xyz();
  
    <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;std::vector&lt;Real&gt; &gt;&amp; phi = fe-&gt;get_phi();
  
    <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;std::vector&lt;RealGradient&gt; &gt;&amp; dphi = fe-&gt;get_dphi();
  
    DenseMatrix&lt;Number&gt; Ke;
    DenseVector&lt;Number&gt; Fe;
  
    <B><FONT COLOR="#5F9EA0">std</FONT></B>::vector&lt;<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B>&gt; dof_indices, dof_indices2;
  
    <B><FONT COLOR="#5F9EA0">MeshBase</FONT></B>::const_element_iterator       el     = mesh.local_elements_begin();
    <B><FONT COLOR="#228B22">const</FONT></B> MeshBase::const_element_iterator end_el = mesh.local_elements_end();
  
    <B><FONT COLOR="#A020F0">for</FONT></B> ( ; el != end_el; ++el)
      {
        perf_log.push(<B><FONT COLOR="#BC8F8F">&quot;elem init&quot;</FONT></B>);      
  
        <B><FONT COLOR="#228B22">const</FONT></B> Elem* elem = *el;
  
        dof_map.dof_indices (elem, dof_indices,0);
        dof_map.dof_indices (elem, dof_indices2,1);
  
  
        fe-&gt;reinit (elem);
  
        Ke.resize (std::max(dof_indices.size(), dof_indices2.size()),
  		 <B><FONT COLOR="#5F9EA0">std</FONT></B>::max(dof_indices.size(), dof_indices2.size()));
  
        Fe.resize (std::max(dof_indices.size(), dof_indices2.size()));
  
        perf_log.pop(<B><FONT COLOR="#BC8F8F">&quot;elem init&quot;</FONT></B>);      
  
        perf_log.push (<B><FONT COLOR="#BC8F8F">&quot;Ke&quot;</FONT></B>);
  
        <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> qp=0; qp&lt;qrule.n_points(); qp++)
          <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i=0; i&lt;phi.size(); i++)
            <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> j=0; j&lt;phi.size(); j++)
              Ke(i,j) += JxW[qp]*(dphi[i][qp]*dphi[j][qp]);
              
  
        perf_log.pop (<B><FONT COLOR="#BC8F8F">&quot;Ke&quot;</FONT></B>);
  
        perf_log.push (<B><FONT COLOR="#BC8F8F">&quot;Fe&quot;</FONT></B>);
        
        <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> qp=0; qp&lt;qrule.n_points(); qp++)
          {
            <B><FONT COLOR="#228B22">const</FONT></B> Real x = q_point[qp](0);
  #<B><FONT COLOR="#A020F0">if</FONT></B> LIBMESH_DIM &gt; 1
            <B><FONT COLOR="#228B22">const</FONT></B> Real y = q_point[qp](1);
  #<B><FONT COLOR="#A020F0">else</FONT></B>
            <B><FONT COLOR="#228B22">const</FONT></B> Real y = 0;
  #endif
  #<B><FONT COLOR="#A020F0">if</FONT></B> LIBMESH_DIM &gt; 2
            <B><FONT COLOR="#228B22">const</FONT></B> Real z = q_point[qp](2);
  #<B><FONT COLOR="#A020F0">else</FONT></B>
            <B><FONT COLOR="#228B22">const</FONT></B> Real z = 0;
  #endif
            <B><FONT COLOR="#228B22">const</FONT></B> Real eps = 1.e-3;
  
            <B><FONT COLOR="#228B22">const</FONT></B> Real uxx = (exact_solution(x-eps,y,z) +
                              exact_solution(x+eps,y,z) +
                              -2.*exact_solution(x,y,z))/eps/eps;
                
            <B><FONT COLOR="#228B22">const</FONT></B> Real uyy = (exact_solution(x,y-eps,z) +
                              exact_solution(x,y+eps,z) +
                              -2.*exact_solution(x,y,z))/eps/eps;
            
            <B><FONT COLOR="#228B22">const</FONT></B> Real uzz = (exact_solution(x,y,z-eps) +
                              exact_solution(x,y,z+eps) +
                              -2.*exact_solution(x,y,z))/eps/eps;
  
            Real fxy;
            <B><FONT COLOR="#A020F0">if</FONT></B>(dim==1)
            {
              <B><FONT COLOR="#228B22">const</FONT></B> Real pi = libMesh::pi;
              fxy = (0.25*pi*pi)*sin(.5*pi*x);
            }
            <B><FONT COLOR="#A020F0">else</FONT></B>
            {
              fxy = - (uxx + uyy + ((dim==2) ? 0. : uzz));
            } 
  
            <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i=0; i&lt;phi.size(); i++)
              Fe(i) += JxW[qp]*fxy*phi[i][qp];          
          }
        
        perf_log.pop (<B><FONT COLOR="#BC8F8F">&quot;Fe&quot;</FONT></B>);
  
        {
          
          perf_log.push (<B><FONT COLOR="#BC8F8F">&quot;BCs&quot;</FONT></B>);
  
          <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> side=0; side&lt;elem-&gt;n_sides(); side++)
            <B><FONT COLOR="#A020F0">if</FONT></B> ((elem-&gt;neighbor(side) == NULL) ||
  	      (elem-&gt;neighbor(side)-&gt;subdomain_id() != elem-&gt;subdomain_id()))
              {
              
                <B><FONT COLOR="#228B22">const</FONT></B> Real penalty = 1.e10;
  
                <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;std::vector&lt;Real&gt; &gt;&amp;  phi_face = fe_face-&gt;get_phi();
  
                <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;Real&gt;&amp; JxW_face = fe_face-&gt;get_JxW();
  
                <B><FONT COLOR="#228B22">const</FONT></B> std::vector&lt;Point &gt;&amp; qface_point = fe_face-&gt;get_xyz();
  
                fe_face-&gt;reinit(elem, side);
  
                <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> qp=0; qp&lt;qface.n_points(); qp++)
                {
                  <B><FONT COLOR="#228B22">const</FONT></B> Real xf = qface_point[qp](0);
  #<B><FONT COLOR="#A020F0">if</FONT></B> LIBMESH_DIM &gt; 1
                  <B><FONT COLOR="#228B22">const</FONT></B> Real yf = qface_point[qp](1);
  #<B><FONT COLOR="#A020F0">else</FONT></B>
                  <B><FONT COLOR="#228B22">const</FONT></B> Real yf = 0.;
  #endif
  #<B><FONT COLOR="#A020F0">if</FONT></B> LIBMESH_DIM &gt; 2
                  <B><FONT COLOR="#228B22">const</FONT></B> Real zf = qface_point[qp](2);
  #<B><FONT COLOR="#A020F0">else</FONT></B>
                  <B><FONT COLOR="#228B22">const</FONT></B> Real zf = 0.;
  #endif
  
  
                  <B><FONT COLOR="#228B22">const</FONT></B> Real value = exact_solution(xf, yf, zf);
  
                  <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i=0; i&lt;phi_face.size(); i++)
                    <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> j=0; j&lt;phi_face.size(); j++)
                      Ke(i,j) += JxW_face[qp]*penalty*phi_face[i][qp]*phi_face[j][qp];
  
                  <B><FONT COLOR="#A020F0">for</FONT></B> (<B><FONT COLOR="#228B22">unsigned</FONT></B> <B><FONT COLOR="#228B22">int</FONT></B> i=0; i&lt;phi_face.size(); i++)
                    Fe(i) += JxW_face[qp]*penalty*value*phi_face[i][qp];
                } 
              }
              
          
          perf_log.pop (<B><FONT COLOR="#BC8F8F">&quot;BCs&quot;</FONT></B>);
        } 
        
  
        perf_log.push (<B><FONT COLOR="#BC8F8F">&quot;matrix insertion&quot;</FONT></B>);
        
        <B><FONT COLOR="#A020F0">if</FONT></B> (dof_indices.size())
  	{
  	  system.matrix-&gt;add_matrix (Ke, dof_indices);
  	  system.rhs-&gt;add_vector    (Fe, dof_indices);
  	}
        
        <B><FONT COLOR="#A020F0">if</FONT></B> (dof_indices2.size())
  	{
  	  system.matrix-&gt;add_matrix (Ke, dof_indices2);
  	  system.rhs-&gt;add_vector    (Fe, dof_indices2);
  	}
  
        perf_log.pop (<B><FONT COLOR="#BC8F8F">&quot;matrix insertion&quot;</FONT></B>);
      }
  
  }
</pre> 
<a name="output"></a> 
<br><br><br> <h1> The console output of the program: </h1> 
<pre>
Linking subdomains_ex2-opt...
***************************************************************
* Running Example  mpirun -np 6 ./subdomains_ex2-opt -pc_type bjacobi -sub_pc_type ilu -sub_pc_factor_levels 4 -sub_pc_factor_zeropivot 0 -ksp_right_pc -log_summary
***************************************************************
 
Running ./subdomains_ex2-opt -d 1 -n 20 -pc_type bjacobi -sub_pc_type ilu -sub_pc_factor_levels 4 -sub_pc_factor_zeropivot 0 -ksp_right_pc -log_summary

 Mesh Information:
  mesh_dimension()=1
  spatial_dimension()=3
  n_nodes()=41
    n_local_nodes()=7
  n_elem()=20
    n_local_elem()=3
    n_active_elem()=20
  n_subdomains()=2
  n_partitions()=6
  n_processors()=6
  n_threads()=1
  processor_id()=0

 EquationSystems
  n_systems()=1
   System #0, "Poisson"
    Type "LinearImplicit"
    Variables="u" "v" 
    Finite Element Types="LAGRANGE", "JACOBI_20_00" "LAGRANGE", "JACOBI_20_00" 
    Infinite Element Mapping="CARTESIAN" "CARTESIAN" 
    Approximation Orders="SECOND", "THIRD" "SECOND", "THIRD" 
    n_dofs()=42
    n_local_dofs()=7
    n_constrained_dofs()=0
    n_local_constrained_dofs()=0
    n_vectors()=1
    n_matrices()=1
    DofMap Sparsity
      Average  On-Processor Bandwidth <= 3.57143
      Average Off-Processor Bandwidth <= 0.285714
      Maximum  On-Processor Bandwidth <= 5
      Maximum Off-Processor Bandwidth <= 2
    DofMap Constraints
      Number of DoF Constraints = 0
      Number of Node Constraints = 0

 Mesh Information:
  mesh_dimension()=1
  spatial_dimension()=3
  n_nodes()=41
    n_local_nodes()=7
  n_elem()=20
    n_local_elem()=3
    n_active_elem()=20
  n_subdomains()=2
  n_partitions()=6
  n_processors()=6
  n_threads()=1
  processor_id()=0


-------------------------------------------------------------------
| Processor id:   0                                                |
| Num Processors: 6                                                |
| Time:           Fri Aug 24 15:26:17 2012                         |
| OS:             Linux                                            |
| HostName:       daedalus                                         |
| OS Release:     2.6.32-34-generic                                |
| OS Version:     #76-Ubuntu SMP Tue Aug 30 17:05:01 UTC 2011      |
| Machine:        x86_64                                           |
| Username:       roystgnr                                         |
| Configuration:  ./configure run on Wed Aug 22 12:44:06 CDT 2012  |
-------------------------------------------------------------------
 -----------------------------------------------------------------------------------------------------------
| Matrix Assembly Performance: Alive time=0.000474, Active time=0.000198                                    |
 -----------------------------------------------------------------------------------------------------------
| Event                         nCalls    Total Time  Avg Time    Total Time  Avg Time    % of Active Time  |
|                                         w/o Sub     w/o Sub     With Sub    With Sub    w/o S    With S   |
|-----------------------------------------------------------------------------------------------------------|
|                                                                                                           |
| BCs                           3         0.0000      0.000014    0.0000      0.000014    20.71    20.71    |
| Fe                            3         0.0001      0.000020    0.0001      0.000020    30.30    30.30    |
| Ke                            3         0.0000      0.000000    0.0000      0.000000    0.51     0.51     |
| elem init                     3         0.0001      0.000025    0.0001      0.000025    38.38    38.38    |
| matrix insertion              3         0.0000      0.000007    0.0000      0.000007    10.10    10.10    |
 -----------------------------------------------------------------------------------------------------------
| Totals:                       15        0.0002                                          100.00            |
 -----------------------------------------------------------------------------------------------------------

************************************************************************************************************************
***             WIDEN YOUR WINDOW TO 120 CHARACTERS.  Use 'enscript -r -fCourier9' to print this document            ***
************************************************************************************************************************

---------------------------------------------- PETSc Performance Summary: ----------------------------------------------

./subdomains_ex2-opt on a intel-11. named daedalus with 6 processors, by roystgnr Fri Aug 24 15:26:17 2012
Using Petsc Release Version 3.1.0, Patch 5, Mon Sep 27 11:51:54 CDT 2010

                         Max       Max/Min        Avg      Total 
Time (sec):           8.059e-02      3.28891   3.420e-02
Objects:              4.300e+01      1.04878   4.233e+01
Flops:                3.975e+03      1.50739   3.117e+03  1.870e+04
Flops/sec:            1.602e+05      4.12037   1.107e+05  6.645e+05
MPI Messages:         3.700e+01      2.00000   3.083e+01  1.850e+02
MPI Message Lengths:  4.220e+02      2.02885   1.131e+01  2.092e+03
MPI Reductions:       7.100e+01      1.02899

Flop counting convention: 1 flop = 1 real number operation of type (multiply/divide/add/subtract)
                            e.g., VecAXPY() for real vectors of length N --> 2N flops
                            and VecAXPY() for complex vectors of length N --> 8N flops

Summary of Stages:   ----- Time ------  ----- Flops -----  --- Messages ---  -- Message Lengths --  -- Reductions --
                        Avg     %Total     Avg     %Total   counts   %Total     Avg         %Total   counts   %Total 
 0:      Main Stage: 3.4152e-02  99.9%  1.8702e+04 100.0%  1.850e+02 100.0%  1.131e+01      100.0%  5.433e+01  76.5% 

------------------------------------------------------------------------------------------------------------------------
See the 'Profiling' chapter of the users' manual for details on interpreting output.
Phase summary info:
   Count: number of times phase was executed
   Time and Flops: Max - maximum over all processors
                   Ratio - ratio of maximum to minimum over all processors
   Mess: number of messages sent
   Avg. len: average message length
   Reduct: number of global reductions
   Global: entire computation
   Stage: stages of a computation. Set stages with PetscLogStagePush() and PetscLogStagePop().
      %T - percent time in this phase         %F - percent flops in this phase
      %M - percent messages in this phase     %L - percent message lengths in this phase
      %R - percent reductions in this phase
   Total Mflop/s: 10e-6 * (sum of flops over all processors)/(max time over all processors)
------------------------------------------------------------------------------------------------------------------------
Event                Count      Time (sec)     Flops                             --- Global ---  --- Stage ---   Total
                   Max Ratio  Max     Ratio   Max  Ratio  Mess   Avg len Reduct  %T %F %M %L %R  %T %F %M %L %R Mflop/s
------------------------------------------------------------------------------------------------------------------------

--- Event Stage 0: Main Stage

VecMDot               10 1.0 2.9802e-04 5.8 9.35e+02 1.5 0.0e+00 0.0e+00 1.0e+01  1 23  0  0 14   1 23  0  0 18    14
VecNorm               12 1.0 1.3173e-0313.3 2.16e+02 1.5 0.0e+00 0.0e+00 1.2e+01  3  5  0  0 17   3  5  0  0 22     1
VecScale              11 1.0 4.0770e-05 2.4 9.90e+01 1.5 0.0e+00 0.0e+00 0.0e+00  0  2  0  0  0   0  2  0  0  0    11
VecCopy                4 1.0 3.0994e-06 1.6 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
VecSet                16 1.0 1.2159e-05 1.6 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
VecAXPY                2 1.0 7.6210e-03 1.2 3.60e+01 1.5 0.0e+00 0.0e+00 0.0e+00 20  1  0  0  0  20  1  0  0  0     0
VecMAXPY              11 1.0 3.8147e-06 4.0 1.17e+03 1.5 0.0e+00 0.0e+00 0.0e+00  0 29  0  0  0   0 29  0  0  0  1431
VecAssemblyBegin       3 1.0 1.0452e-03 2.4 0.00e+00 0.0 1.0e+01 6.0e+00 9.0e+00  3  0  5  3 13   3  0  5  3 17     0
VecAssemblyEnd         3 1.0 3.3140e-05 2.0 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
VecScatterBegin       12 1.0 8.1539e-05 2.2 0.00e+00 0.0 1.2e+02 1.3e+01 0.0e+00  0  0 65 73  0   0  0 65 73  0     0
VecScatterEnd         12 1.0 1.2898e-04 8.3 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
VecNormalize          11 1.0 1.3499e-0312.3 2.97e+02 1.5 0.0e+00 0.0e+00 1.1e+01  3  7  0  0 15   3  7  0  0 20     1
MatMult               11 1.0 2.0528e-04 2.7 6.27e+02 1.4 1.1e+02 1.2e+01 0.0e+00  0 17 59 63  0   0 17 59 63  0    15
MatSolve              11 1.0 1.0014e-05 1.5 8.03e+02 1.6 0.0e+00 0.0e+00 0.0e+00  0 20  0  0  0   0 20  0  0  0   380
MatLUFactorNum         1 1.0 3.6001e-05 2.4 8.90e+01 1.8 0.0e+00 0.0e+00 0.0e+00  0  2  0  0  0   0  2  0  0  0    11
MatILUFactorSym        1 1.0 7.0095e-05 1.4 0.00e+00 0.0 0.0e+00 0.0e+00 1.0e+00  0  0  0  0  1   0  0  0  0  2     0
MatAssemblyBegin       2 1.0 1.0891e-03 4.0 0.00e+00 0.0 1.5e+01 1.7e+01 4.0e+00  3  0  8 12  6   3  0  8 12  7     0
MatAssemblyEnd         2 1.0 1.0631e-03 1.1 0.00e+00 0.0 2.0e+01 5.0e+00 8.0e+00  3  0 11  5 11   3  0 11  5 15     0
MatGetRowIJ            1 1.0 8.1062e-06 8.5 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
MatGetOrdering         1 1.0 6.6042e-05 1.8 0.00e+00 0.0 0.0e+00 0.0e+00 3.3e+00  0  0  0  0  5   0  0  0  0  6     0
MatZeroEntries         3 1.0 1.3828e-05 1.8 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
KSPGMRESOrthog        10 1.0 3.1900e-04 3.2 1.92e+03 1.5 0.0e+00 0.0e+00 1.0e+01  1 48  0  0 14   1 48  0  0 18    28
KSPSetup               2 1.0 8.1062e-05 1.4 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
KSPSolve               1 1.0 9.1660e-03 1.0 3.98e+03 1.5 1.1e+02 1.2e+01 2.6e+01 27100 59 63 37  27100 59 63 48     2
PCSetUp                2 1.0 5.4502e-04 1.4 8.90e+01 1.8 0.0e+00 0.0e+00 4.3e+00  1  2  0  0  6   1  2  0  0  8     1
PCSetUpOnBlocks        1 1.0 2.4605e-04 1.4 8.90e+01 1.8 0.0e+00 0.0e+00 4.3e+00  1  2  0  0  6   1  2  0  0  8     2
PCApply               11 1.0 1.8477e-04 1.3 8.03e+02 1.6 0.0e+00 0.0e+00 0.0e+00  0 20  0  0  0   0 20  0  0  0    21
------------------------------------------------------------------------------------------------------------------------

Memory usage is given in bytes:

Object Type          Creations   Destructions     Memory  Descendants' Mem.
Reports information only for process 0.

--- Event Stage 0: Main Stage

                 Vec    23             23        31696     0
         Vec Scatter     2              2         1736     0
           Index Set     9              9         4820     0
   IS L to G Mapping     1              1          440     0
              Matrix     4              4         9732     0
       Krylov Solver     2              2        18880     0
      Preconditioner     2              2         1408     0
========================================================================================================================
Average time to get PetscTime(): 0
Average time for MPI_Barrier(): 4.27723e-05
Average time for zero size MPI_Send(): 4.51803e-05
#PETSc Option Table entries:
-d 1
-ksp_right_pc
-log_summary
-n 20
-pc_type bjacobi
-sub_pc_factor_levels 4
-sub_pc_factor_zeropivot 0
-sub_pc_type ilu
#End of PETSc Option Table entries
Compiled without FORTRAN kernels
Compiled with full precision matrices (default)
sizeof(short) 2 sizeof(int) 4 sizeof(long) 8 sizeof(void*) 8 sizeof(PetscScalar) 8
Configure run at: Sat May 19 03:47:23 2012
Configure options: --with-debugging=false --COPTFLAGS=-O3 --CXXOPTFLAGS=-O3 --FOPTFLAGS=-O3 --with-clanguage=C++ --with-shared=1 --with-shared-libraries=1 --with-mpi-dir=/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid --with-mumps=true --download-mumps=1 --with-parmetis=true --download-parmetis=1 --with-superlu=true --download-superlu=1 --with-superludir=true --download-superlu_dist=1 --with-blacs=true --download-blacs=1 --with-scalapack=true --download-scalapack=1 --with-hypre=true --download-hypre=1 --with-blas-lib="[/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_intel_lp64.so,/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_sequential.so,/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_core.so]" --with-lapack-lib=/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_solver_lp64_sequential.a
-----------------------------------------
Libraries compiled on Sat May 19 03:47:23 CDT 2012 on daedalus 
Machine characteristics: Linux daedalus 2.6.32-34-generic #76-Ubuntu SMP Tue Aug 30 17:05:01 UTC 2011 x86_64 GNU/Linux 
Using PETSc directory: /org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5
Using PETSc arch: intel-11.1-lucid-mpich2-1.4.1-cxx-opt
-----------------------------------------
Using C compiler: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpicxx -O3   -fPIC   
Using Fortran compiler: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpif90 -fPIC -O3    
-----------------------------------------
Using include paths: -I/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/include -I/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/include -I/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/include -I/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/include  
------------------------------------------
Using C linker: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpicxx -O3 
Using Fortran linker: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpif90 -fPIC -O3  
Using libraries: -Wl,-rpath,/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -L/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -lpetsc       -lX11 -Wl,-rpath,/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -L/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -lHYPRE -lcmumps -ldmumps -lsmumps -lzmumps -lmumps_common -lpord -lscalapack -lblacs -lsuperlu_dist_2.4 -lparmetis -lmetis -lsuperlu_4.0 -Wl,-rpath,/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t -L/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t -lmkl_solver_lp64_sequential -lmkl_intel_lp64 -lmkl_sequential -lmkl_core -ldl -Wl,-rpath,/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/lib -L/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/lib -lmpich -lopa -lmpl -lrt -lpthread -Wl,-rpath,/opt/intel/Compiler/11.1/073/lib/intel64 -L/opt/intel/Compiler/11.1/073/lib/intel64 -Wl,-rpath,/usr/lib/gcc/x86_64-linux-gnu/4.4.3 -L/usr/lib/gcc/x86_64-linux-gnu/4.4.3 -limf -lsvml -lipgo -ldecimal -lgcc_s -lirc -lirc_s -lmpichf90 -lifport -lifcore -lm -lm -lmpichcxx -lstdc++ -lmpichcxx -lstdc++ -ldl -lmpich -lopa -lmpl -lrt -lpthread -limf -lsvml -lipgo -ldecimal -lgcc_s -lirc -lirc_s -ldl  
------------------------------------------
 ----------------------------------------------------------------------------------------------------------------
| libMesh Performance: Alive time=0.269521, Active time=0.074369                                                 |
 ----------------------------------------------------------------------------------------------------------------
| Event                              nCalls    Total Time  Avg Time    Total Time  Avg Time    % of Active Time  |
|                                              w/o Sub     w/o Sub     With Sub    With Sub    w/o S    With S   |
|----------------------------------------------------------------------------------------------------------------|
|                                                                                                                |
|                                                                                                                |
| DofMap                                                                                                         |
|   add_neighbors_to_send_list()     1         0.0000      0.000013    0.0000      0.000015    0.02     0.02     |
|   build_sparsity()                 1         0.0001      0.000065    0.0001      0.000069    0.09     0.09     |
|   create_dof_constraints()         1         0.0000      0.000001    0.0000      0.000001    0.00     0.00     |
|   distribute_dofs()                1         0.0001      0.000066    0.0006      0.000595    0.09     0.80     |
|   dof_indices()                    31        0.0000      0.000000    0.0000      0.000000    0.02     0.02     |
|   prepare_send_list()              1         0.0000      0.000001    0.0000      0.000001    0.00     0.00     |
|   reinit()                         1         0.0000      0.000039    0.0000      0.000039    0.05     0.05     |
|                                                                                                                |
| EquationSystems                                                                                                |
|   build_solution_vector()          1         0.0001      0.000088    0.0005      0.000522    0.12     0.70     |
|                                                                                                                |
| FE                                                                                                             |
|   compute_shape_functions()        4         0.0000      0.000002    0.0000      0.000002    0.01     0.01     |
|   init_shape_functions()           2         0.0000      0.000005    0.0000      0.000005    0.01     0.01     |
|                                                                                                                |
| FEMap                                                                                                          |
|   compute_affine_map()             4         0.0000      0.000002    0.0000      0.000002    0.01     0.01     |
|   compute_face_map()               1         0.0000      0.000002    0.0000      0.000002    0.00     0.00     |
|   init_face_shape_functions()      1         0.0000      0.000004    0.0000      0.000004    0.01     0.01     |
|   init_reference_to_physical_map() 2         0.0000      0.000005    0.0000      0.000005    0.01     0.01     |
|                                                                                                                |
| GnuPlotIO                                                                                                      |
|   write_nodal_data()               1         0.0562      0.056166    0.0562      0.056166    75.52    75.52    |
|                                                                                                                |
| Mesh                                                                                                           |
|   find_neighbors()                 1         0.0001      0.000052    0.0001      0.000090    0.07     0.12     |
|   renumber_nodes_and_elem()        2         0.0000      0.000003    0.0000      0.000003    0.01     0.01     |
|                                                                                                                |
| MeshCommunication                                                                                              |
|   compute_hilbert_indices()        2         0.0002      0.000080    0.0002      0.000080    0.21     0.21     |
|   find_global_indices()            2         0.0001      0.000033    0.0027      0.001350    0.09     3.63     |
|   parallel_sort()                  2         0.0013      0.000660    0.0015      0.000764    1.78     2.05     |
|                                                                                                                |
| MeshOutput                                                                                                     |
|   write_equation_systems()         1         0.0000      0.000021    0.0567      0.056709    0.03     76.25    |
|                                                                                                                |
| MeshTools::Generation                                                                                          |
|   build_cube()                     1         0.0000      0.000034    0.0000      0.000034    0.05     0.05     |
|                                                                                                                |
| MetisPartitioner                                                                                               |
|   partition()                      1         0.0003      0.000270    0.0013      0.001340    0.36     1.80     |
|                                                                                                                |
| Parallel                                                                                                       |
|   allgather()                      9         0.0004      0.000039    0.0004      0.000039    0.47     0.47     |
|   broadcast()                      2         0.0000      0.000005    0.0000      0.000005    0.01     0.01     |
|   gather()                         2         0.0001      0.000066    0.0001      0.000066    0.18     0.18     |
|   max(scalar)                      2         0.0000      0.000021    0.0000      0.000021    0.06     0.06     |
|   max(vector)                      2         0.0001      0.000035    0.0001      0.000035    0.09     0.09     |
|   min(vector)                      2         0.0001      0.000040    0.0001      0.000040    0.11     0.11     |
|   probe()                          50        0.0014      0.000027    0.0014      0.000027    1.85     1.85     |
|   receive()                        50        0.0001      0.000002    0.0015      0.000029    0.11     1.96     |
|   send()                           50        0.0000      0.000001    0.0000      0.000001    0.05     0.05     |
|   send_receive()                   54        0.0001      0.000002    0.0016      0.000030    0.14     2.21     |
|   sum()                            12        0.0008      0.000063    0.0008      0.000063    1.01     1.01     |
|                                                                                                                |
| Parallel::Request                                                                                              |
|   wait()                           50        0.0000      0.000001    0.0000      0.000001    0.05     0.05     |
|                                                                                                                |
| Partitioner                                                                                                    |
|   set_node_processor_ids()         1         0.0000      0.000027    0.0005      0.000504    0.04     0.68     |
|   set_parent_processor_ids()       1         0.0000      0.000005    0.0000      0.000005    0.01     0.01     |
|                                                                                                                |
| PetscLinearSolver                                                                                              |
|   solve()                          1         0.0122      0.012155    0.0122      0.012155    16.34    16.34    |
|                                                                                                                |
| System                                                                                                         |
|   assemble()                       1         0.0007      0.000685    0.0007      0.000735    0.92     0.99     |
 ----------------------------------------------------------------------------------------------------------------
| Totals:                            354       0.0744                                          100.00            |
 ----------------------------------------------------------------------------------------------------------------

Running ./subdomains_ex2-opt -d 2 -n 15 -pc_type bjacobi -sub_pc_type ilu -sub_pc_factor_levels 4 -sub_pc_factor_zeropivot 0 -ksp_right_pc -log_summary

 Mesh Information:
  mesh_dimension()=2
  spatial_dimension()=3
  n_nodes()=961
    n_local_nodes()=175
  n_elem()=225
    n_local_elem()=37
    n_active_elem()=225
  n_subdomains()=2
  n_partitions()=6
  n_processors()=6
  n_threads()=1
  processor_id()=0

 EquationSystems
  n_systems()=1
   System #0, "Poisson"
    Type "LinearImplicit"
    Variables="u" "v" 
    Finite Element Types="LAGRANGE", "JACOBI_20_00" "LAGRANGE", "JACOBI_20_00" 
    Infinite Element Mapping="CARTESIAN" "CARTESIAN" 
    Approximation Orders="SECOND", "THIRD" "SECOND", "THIRD" 
    n_dofs()=1022
    n_local_dofs()=188
    n_constrained_dofs()=0
    n_local_constrained_dofs()=0
    n_vectors()=1
    n_matrices()=1
    DofMap Sparsity
      Average  On-Processor Bandwidth <= 13.4574
      Average Off-Processor Bandwidth <= 1.20213
      Maximum  On-Processor Bandwidth <= 25
      Maximum Off-Processor Bandwidth <= 14
    DofMap Constraints
      Number of DoF Constraints = 0
      Number of Node Constraints = 0

 Mesh Information:
  mesh_dimension()=2
  spatial_dimension()=3
  n_nodes()=961
    n_local_nodes()=175
  n_elem()=225
    n_local_elem()=37
    n_active_elem()=225
  n_subdomains()=2
  n_partitions()=6
  n_processors()=6
  n_threads()=1
  processor_id()=0


-------------------------------------------------------------------
| Processor id:   0                                                |
| Num Processors: 6                                                |
| Time:           Fri Aug 24 15:26:18 2012                         |
| OS:             Linux                                            |
| HostName:       daedalus                                         |
| OS Release:     2.6.32-34-generic                                |
| OS Version:     #76-Ubuntu SMP Tue Aug 30 17:05:01 UTC 2011      |
| Machine:        x86_64                                           |
| Username:       roystgnr                                         |
| Configuration:  ./configure run on Wed Aug 22 12:44:06 CDT 2012  |
-------------------------------------------------------------------
 -----------------------------------------------------------------------------------------------------------
| Matrix Assembly Performance: Alive time=0.001896, Active time=0.001541                                    |
 -----------------------------------------------------------------------------------------------------------
| Event                         nCalls    Total Time  Avg Time    Total Time  Avg Time    % of Active Time  |
|                                         w/o Sub     w/o Sub     With Sub    With Sub    w/o S    With S   |
|-----------------------------------------------------------------------------------------------------------|
|                                                                                                           |
| BCs                           37        0.0005      0.000015    0.0005      0.000015    34.85    34.85    |
| Fe                            37        0.0002      0.000005    0.0002      0.000005    12.39    12.39    |
| Ke                            37        0.0002      0.000005    0.0002      0.000005    11.10    11.10    |
| elem init                     37        0.0005      0.000014    0.0005      0.000014    32.51    32.51    |
| matrix insertion              37        0.0001      0.000004    0.0001      0.000004    9.15     9.15     |
 -----------------------------------------------------------------------------------------------------------
| Totals:                       185       0.0015                                          100.00            |
 -----------------------------------------------------------------------------------------------------------

************************************************************************************************************************
***             WIDEN YOUR WINDOW TO 120 CHARACTERS.  Use 'enscript -r -fCourier9' to print this document            ***
************************************************************************************************************************

---------------------------------------------- PETSc Performance Summary: ----------------------------------------------

./subdomains_ex2-opt on a intel-11. named daedalus with 6 processors, by roystgnr Fri Aug 24 15:26:18 2012
Using Petsc Release Version 3.1.0, Patch 5, Mon Sep 27 11:51:54 CDT 2010

                         Max       Max/Min        Avg      Total 
Time (sec):           4.524e-02      1.03149   4.418e-02
Objects:              5.300e+01      1.03922   5.267e+01
Flops:                7.303e+05      1.84249   6.029e+05  3.617e+06
Flops/sec:            1.663e+07      1.84002   1.363e+07  8.180e+07
MPI Messages:         1.125e+02      2.50000   6.800e+01  4.080e+02
MPI Message Lengths:  1.620e+04      1.66386   1.845e+02  7.527e+04
MPI Reductions:       7.900e+01      1.02597

Flop counting convention: 1 flop = 1 real number operation of type (multiply/divide/add/subtract)
                            e.g., VecAXPY() for real vectors of length N --> 2N flops
                            and VecAXPY() for complex vectors of length N --> 8N flops

Summary of Stages:   ----- Time ------  ----- Flops -----  --- Messages ---  -- Message Lengths --  -- Reductions --
                        Avg     %Total     Avg     %Total   counts   %Total     Avg         %Total   counts   %Total 
 0:      Main Stage: 4.4135e-02  99.9%  3.6172e+06 100.0%  4.080e+02 100.0%  1.845e+02      100.0%  6.267e+01  79.3% 

------------------------------------------------------------------------------------------------------------------------
See the 'Profiling' chapter of the users' manual for details on interpreting output.
Phase summary info:
   Count: number of times phase was executed
   Time and Flops: Max - maximum over all processors
                   Ratio - ratio of maximum to minimum over all processors
   Mess: number of messages sent
   Avg. len: average message length
   Reduct: number of global reductions
   Global: entire computation
   Stage: stages of a computation. Set stages with PetscLogStagePush() and PetscLogStagePop().
      %T - percent time in this phase         %F - percent flops in this phase
      %M - percent messages in this phase     %L - percent message lengths in this phase
      %R - percent reductions in this phase
   Total Mflop/s: 10e-6 * (sum of flops over all processors)/(max time over all processors)
------------------------------------------------------------------------------------------------------------------------
Event                Count      Time (sec)     Flops                             --- Global ---  --- Stage ---   Total
                   Max Ratio  Max     Ratio   Max  Ratio  Mess   Avg len Reduct  %T %F %M %L %R  %T %F %M %L %R Mflop/s
------------------------------------------------------------------------------------------------------------------------

--- Event Stage 0: Main Stage

VecMDot               14 1.0 1.2534e-03 2.6 4.06e+04 1.3 0.0e+00 0.0e+00 1.4e+01  2  6  0  0 18   2  6  0  0 22   171
VecNorm               16 1.0 6.5002e-03 4.8 6.21e+03 1.3 0.0e+00 0.0e+00 1.6e+01 10  1  0  0 20  10  1  0  0 26     5
VecScale              15 1.0 4.2439e-05 1.9 2.91e+03 1.3 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0   361
VecCopy                4 1.0 5.0068e-06 2.3 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
VecSet                20 1.0 1.4067e-05 1.9 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
VecAXPY                2 1.0 1.0614e-02 2.0 7.76e+02 1.3 0.0e+00 0.0e+00 0.0e+00 17  0  0  0  0  17  0  0  0  0     0
VecMAXPY              15 1.0 2.0504e-05 1.6 4.62e+04 1.3 0.0e+00 0.0e+00 0.0e+00  0  7  0  0  0   0  7  0  0  0 11863
VecAssemblyBegin       3 1.0 7.0214e-04 1.3 0.00e+00 0.0 1.8e+01 1.2e+02 9.0e+00  1  0  4  3 11   1  0  4  3 14     0
VecAssemblyEnd         3 1.0 2.4080e-05 1.1 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
VecScatterBegin       16 1.0 7.4863e-05 1.6 0.00e+00 0.0 2.9e+02 1.6e+02 0.0e+00  0  0 71 62  0   0  0 71 62  0     0
VecScatterEnd         16 1.0 3.3181e-0369.9 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  4  0  0  0  0   4  0  0  0  0     0
VecNormalize          15 1.0 6.5413e-03 4.6 8.73e+03 1.3 0.0e+00 0.0e+00 1.5e+01 10  1  0  0 19  10  1  0  0 24     7
MatMult               15 1.0 3.3698e-0315.3 8.24e+04 1.4 2.7e+02 1.5e+02 0.0e+00  4 12 66 55  0   4 12 66 55  0   128
MatSolve              15 1.0 1.8120e-04 1.5 2.83e+05 1.8 0.0e+00 0.0e+00 0.0e+00  0 39  0  0  0   0 39  0  0  0  7754
MatLUFactorNum         1 1.0 3.4904e-04 2.4 2.90e+05 2.8 0.0e+00 0.0e+00 0.0e+00  1 35  0  0  0   1 35  0  0  0  3644
MatILUFactorSym        1 1.0 7.8988e-04 2.1 0.00e+00 0.0 0.0e+00 0.0e+00 1.0e+00  1  0  0  0  1   1  0  0  0  2     0
MatAssemblyBegin       2 1.0 3.9752e-0311.5 0.00e+00 0.0 2.7e+01 8.2e+02 4.0e+00  7  0  7 30  5   7  0  7 30  6     0
MatAssemblyEnd         2 1.0 1.0219e-03 1.7 0.00e+00 0.0 3.6e+01 4.1e+01 8.0e+00  2  0  9  2 10   2  0  9  2 13     0
MatGetRowIJ            1 1.0 9.0599e-06 4.8 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
MatGetOrdering         1 1.0 7.4148e-05 1.6 0.00e+00 0.0 0.0e+00 0.0e+00 3.7e+00  0  0  0  0  5   0  0  0  0  6     0
MatZeroEntries         3 1.0 2.3842e-05 1.9 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
KSPGMRESOrthog        14 1.0 1.2951e-03 2.4 8.14e+04 1.3 0.0e+00 0.0e+00 1.4e+01  2 12  0  0 18   2 12  0  0 22   331
KSPSetup               2 1.0 8.2970e-05 1.3 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
KSPSolve               1 1.0 1.7243e-02 1.0 7.30e+05 1.8 2.7e+02 1.5e+02 3.5e+01 39100 66 55 44  39100 66 55 55   210
PCSetUp                2 1.0 3.1831e-03 3.4 2.90e+05 2.8 0.0e+00 0.0e+00 4.7e+00  4 35  0  0  6   4 35  0  0  7   400
PCSetUpOnBlocks        1 1.0 1.1990e-03 1.8 2.90e+05 2.8 0.0e+00 0.0e+00 4.7e+00  2 35  0  0  6   2 35  0  0  7  1061
PCApply               15 1.0 3.4571e-04 1.2 2.83e+05 1.8 0.0e+00 0.0e+00 0.0e+00  1 39  0  0  0   1 39  0  0  0  4064
------------------------------------------------------------------------------------------------------------------------

Memory usage is given in bytes:

Object Type          Creations   Destructions     Memory  Descendants' Mem.
Reports information only for process 0.

--- Event Stage 0: Main Stage

                 Vec    33             33        87264     0
         Vec Scatter     2              2         1736     0
           Index Set     9              9         8784     0
   IS L to G Mapping     1              1         1420     0
              Matrix     4              4       162252     0
       Krylov Solver     2              2        18880     0
      Preconditioner     2              2         1408     0
========================================================================================================================
Average time to get PetscTime(): 9.53674e-08
Average time for MPI_Barrier(): 4.282e-05
Average time for zero size MPI_Send(): 6.90222e-05
#PETSc Option Table entries:
-d 2
-ksp_right_pc
-log_summary
-n 15
-pc_type bjacobi
-sub_pc_factor_levels 4
-sub_pc_factor_zeropivot 0
-sub_pc_type ilu
#End of PETSc Option Table entries
Compiled without FORTRAN kernels
Compiled with full precision matrices (default)
sizeof(short) 2 sizeof(int) 4 sizeof(long) 8 sizeof(void*) 8 sizeof(PetscScalar) 8
Configure run at: Sat May 19 03:47:23 2012
Configure options: --with-debugging=false --COPTFLAGS=-O3 --CXXOPTFLAGS=-O3 --FOPTFLAGS=-O3 --with-clanguage=C++ --with-shared=1 --with-shared-libraries=1 --with-mpi-dir=/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid --with-mumps=true --download-mumps=1 --with-parmetis=true --download-parmetis=1 --with-superlu=true --download-superlu=1 --with-superludir=true --download-superlu_dist=1 --with-blacs=true --download-blacs=1 --with-scalapack=true --download-scalapack=1 --with-hypre=true --download-hypre=1 --with-blas-lib="[/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_intel_lp64.so,/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_sequential.so,/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_core.so]" --with-lapack-lib=/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_solver_lp64_sequential.a
-----------------------------------------
Libraries compiled on Sat May 19 03:47:23 CDT 2012 on daedalus 
Machine characteristics: Linux daedalus 2.6.32-34-generic #76-Ubuntu SMP Tue Aug 30 17:05:01 UTC 2011 x86_64 GNU/Linux 
Using PETSc directory: /org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5
Using PETSc arch: intel-11.1-lucid-mpich2-1.4.1-cxx-opt
-----------------------------------------
Using C compiler: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpicxx -O3   -fPIC   
Using Fortran compiler: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpif90 -fPIC -O3    
-----------------------------------------
Using include paths: -I/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/include -I/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/include -I/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/include -I/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/include  
------------------------------------------
Using C linker: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpicxx -O3 
Using Fortran linker: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpif90 -fPIC -O3  
Using libraries: -Wl,-rpath,/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -L/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -lpetsc       -lX11 -Wl,-rpath,/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -L/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -lHYPRE -lcmumps -ldmumps -lsmumps -lzmumps -lmumps_common -lpord -lscalapack -lblacs -lsuperlu_dist_2.4 -lparmetis -lmetis -lsuperlu_4.0 -Wl,-rpath,/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t -L/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t -lmkl_solver_lp64_sequential -lmkl_intel_lp64 -lmkl_sequential -lmkl_core -ldl -Wl,-rpath,/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/lib -L/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/lib -lmpich -lopa -lmpl -lrt -lpthread -Wl,-rpath,/opt/intel/Compiler/11.1/073/lib/intel64 -L/opt/intel/Compiler/11.1/073/lib/intel64 -Wl,-rpath,/usr/lib/gcc/x86_64-linux-gnu/4.4.3 -L/usr/lib/gcc/x86_64-linux-gnu/4.4.3 -limf -lsvml -lipgo -ldecimal -lgcc_s -lirc -lirc_s -lmpichf90 -lifport -lifcore -lm -lm -lmpichcxx -lstdc++ -lmpichcxx -lstdc++ -ldl -lmpich -lopa -lmpl -lrt -lpthread -limf -lsvml -lipgo -ldecimal -lgcc_s -lirc -lirc_s -ldl  
------------------------------------------
 ----------------------------------------------------------------------------------------------------------------
| libMesh Performance: Alive time=0.183404, Active time=0.039041                                                 |
 ----------------------------------------------------------------------------------------------------------------
| Event                              nCalls    Total Time  Avg Time    Total Time  Avg Time    % of Active Time  |
|                                              w/o Sub     w/o Sub     With Sub    With Sub    w/o S    With S   |
|----------------------------------------------------------------------------------------------------------------|
|                                                                                                                |
|                                                                                                                |
| DofMap                                                                                                         |
|   add_neighbors_to_send_list()     1         0.0001      0.000068    0.0001      0.000082    0.17     0.21     |
|   build_sparsity()                 1         0.0004      0.000362    0.0005      0.000476    0.93     1.22     |
|   create_dof_constraints()         1         0.0001      0.000052    0.0001      0.000052    0.13     0.13     |
|   distribute_dofs()                1         0.0003      0.000308    0.0020      0.002008    0.79     5.14     |
|   dof_indices()                    365       0.0002      0.000000    0.0002      0.000000    0.44     0.44     |
|   prepare_send_list()              1         0.0000      0.000006    0.0000      0.000006    0.02     0.02     |
|   reinit()                         1         0.0004      0.000411    0.0004      0.000411    1.05     1.05     |
|                                                                                                                |
| EquationSystems                                                                                                |
|   build_solution_vector()          1         0.0001      0.000145    0.0008      0.000835    0.37     2.14     |
|                                                                                                                |
| ExodusII_IO                                                                                                    |
|   write_nodal_data()               1         0.0015      0.001479    0.0015      0.001479    3.79     3.79     |
|                                                                                                                |
| FE                                                                                                             |
|   compute_shape_functions()        55        0.0002      0.000003    0.0002      0.000003    0.49     0.49     |
|   init_shape_functions()           19        0.0000      0.000002    0.0000      0.000002    0.08     0.08     |
|   inverse_map()                    54        0.0001      0.000002    0.0001      0.000002    0.30     0.30     |
|                                                                                                                |
| FEMap                                                                                                          |
|   compute_affine_map()             55        0.0001      0.000001    0.0001      0.000001    0.21     0.21     |
|   compute_face_map()               18        0.0001      0.000005    0.0002      0.000012    0.24     0.56     |
|   init_face_shape_functions()      1         0.0000      0.000002    0.0000      0.000002    0.01     0.01     |
|   init_reference_to_physical_map() 19        0.0001      0.000006    0.0001      0.000006    0.28     0.28     |
|                                                                                                                |
| Mesh                                                                                                           |
|   find_neighbors()                 1         0.0002      0.000203    0.0007      0.000712    0.52     1.82     |
|   renumber_nodes_and_elem()        2         0.0000      0.000022    0.0000      0.000022    0.11     0.11     |
|                                                                                                                |
| MeshCommunication                                                                                              |
|   compute_hilbert_indices()        2         0.0009      0.000450    0.0009      0.000450    2.31     2.31     |
|   find_global_indices()            2         0.0001      0.000075    0.0047      0.002369    0.38     12.14    |
|   parallel_sort()                  2         0.0021      0.001044    0.0028      0.001383    5.35     7.08     |
|                                                                                                                |
| MeshOutput                                                                                                     |
|   write_equation_systems()         1         0.0000      0.000024    0.0023      0.002338    0.06     5.99     |
|                                                                                                                |
| MeshTools::Generation                                                                                          |
|   build_cube()                     1         0.0002      0.000217    0.0002      0.000217    0.56     0.56     |
|                                                                                                                |
| MetisPartitioner                                                                                               |
|   partition()                      1         0.0009      0.000933    0.0027      0.002704    2.39     6.93     |
|                                                                                                                |
| Parallel                                                                                                       |
|   allgather()                      9         0.0022      0.000241    0.0022      0.000241    5.56     5.56     |
|   broadcast()                      2         0.0000      0.000007    0.0000      0.000007    0.04     0.04     |
|   gather()                         2         0.0003      0.000162    0.0003      0.000162    0.83     0.83     |
|   max(scalar)                      2         0.0005      0.000256    0.0005      0.000256    1.31     1.31     |
|   max(vector)                      2         0.0000      0.000017    0.0000      0.000017    0.08     0.08     |
|   min(vector)                      2         0.0001      0.000052    0.0001      0.000052    0.27     0.27     |
|   probe()                          50        0.0019      0.000037    0.0019      0.000037    4.76     4.76     |
|   receive()                        50        0.0001      0.000002    0.0020      0.000039    0.24     5.01     |
|   send()                           50        0.0000      0.000001    0.0000      0.000001    0.12     0.12     |
|   send_receive()                   54        0.0001      0.000002    0.0022      0.000041    0.33     5.63     |
|   sum()                            12        0.0015      0.000122    0.0015      0.000122    3.75     3.75     |
|                                                                                                                |
| Parallel::Request                                                                                              |
|   wait()                           50        0.0000      0.000001    0.0000      0.000001    0.11     0.11     |
|                                                                                                                |
| Partitioner                                                                                                    |
|   set_node_processor_ids()         1         0.0001      0.000086    0.0010      0.000962    0.22     2.46     |
|   set_parent_processor_ids()       1         0.0000      0.000028    0.0000      0.000028    0.07     0.07     |
|                                                                                                                |
| PetscLinearSolver                                                                                              |
|   solve()                          1         0.0225      0.022474    0.0225      0.022474    57.57    57.57    |
|                                                                                                                |
| System                                                                                                         |
|   assemble()                       1         0.0015      0.001470    0.0022      0.002174    3.77     5.57     |
 ----------------------------------------------------------------------------------------------------------------
| Totals:                            895       0.0390                                          100.00            |
 ----------------------------------------------------------------------------------------------------------------

Running ./subdomains_ex2-opt -d 3 -n 6 -pc_type bjacobi -sub_pc_type ilu -sub_pc_factor_levels 4 -sub_pc_factor_zeropivot 0 -ksp_right_pc -log_summary

 Mesh Information:
  mesh_dimension()=3
  spatial_dimension()=3
  n_nodes()=2197
    n_local_nodes()=455
  n_elem()=216
    n_local_elem()=36
    n_active_elem()=216
  n_subdomains()=2
  n_partitions()=6
  n_processors()=6
  n_threads()=1
  processor_id()=0

 EquationSystems
  n_systems()=1
   System #0, "Poisson"
    Type "LinearImplicit"
    Variables="u" "v" 
    Finite Element Types="LAGRANGE", "JACOBI_20_00" "LAGRANGE", "JACOBI_20_00" 
    Infinite Element Mapping="CARTESIAN" "CARTESIAN" 
    Approximation Orders="SECOND", "THIRD" "SECOND", "THIRD" 
    n_dofs()=2522
    n_local_dofs()=520
    n_constrained_dofs()=0
    n_local_constrained_dofs()=0
    n_vectors()=1
    n_matrices()=1
    DofMap Sparsity
      Average  On-Processor Bandwidth <= 41.65
      Average Off-Processor Bandwidth <= 8.48077
      Maximum  On-Processor Bandwidth <= 125
      Maximum Off-Processor Bandwidth <= 60
    DofMap Constraints
      Number of DoF Constraints = 0
      Number of Node Constraints = 0

 Mesh Information:
  mesh_dimension()=3
  spatial_dimension()=3
  n_nodes()=2197
    n_local_nodes()=455
  n_elem()=216
    n_local_elem()=36
    n_active_elem()=216
  n_subdomains()=2
  n_partitions()=6
  n_processors()=6
  n_threads()=1
  processor_id()=0


-------------------------------------------------------------------
| Processor id:   0                                                |
| Num Processors: 6                                                |
| Time:           Fri Aug 24 15:26:18 2012                         |
| OS:             Linux                                            |
| HostName:       daedalus                                         |
| OS Release:     2.6.32-34-generic                                |
| OS Version:     #76-Ubuntu SMP Tue Aug 30 17:05:01 UTC 2011      |
| Machine:        x86_64                                           |
| Username:       roystgnr                                         |
| Configuration:  ./configure run on Wed Aug 22 12:44:06 CDT 2012  |
-------------------------------------------------------------------
 -----------------------------------------------------------------------------------------------------------
| Matrix Assembly Performance: Alive time=0.013579, Active time=0.013158                                    |
 -----------------------------------------------------------------------------------------------------------
| Event                         nCalls    Total Time  Avg Time    Total Time  Avg Time    % of Active Time  |
|                                         w/o Sub     w/o Sub     With Sub    With Sub    w/o S    With S   |
|-----------------------------------------------------------------------------------------------------------|
|                                                                                                           |
| BCs                           36        0.0056      0.000155    0.0056      0.000155    42.42    42.42    |
| Fe                            36        0.0006      0.000016    0.0006      0.000016    4.39     4.39     |
| Ke                            36        0.0038      0.000104    0.0038      0.000104    28.52    28.52    |
| elem init                     36        0.0023      0.000065    0.0023      0.000065    17.69    17.69    |
| matrix insertion              36        0.0009      0.000026    0.0009      0.000026    6.99     6.99     |
 -----------------------------------------------------------------------------------------------------------
| Totals:                       180       0.0132                                          100.00            |
 -----------------------------------------------------------------------------------------------------------

************************************************************************************************************************
***             WIDEN YOUR WINDOW TO 120 CHARACTERS.  Use 'enscript -r -fCourier9' to print this document            ***
************************************************************************************************************************

---------------------------------------------- PETSc Performance Summary: ----------------------------------------------

./subdomains_ex2-opt on a intel-11. named daedalus with 6 processors, by roystgnr Fri Aug 24 15:26:18 2012
Using Petsc Release Version 3.1.0, Patch 5, Mon Sep 27 11:51:54 CDT 2010

                         Max       Max/Min        Avg      Total 
Time (sec):           1.279e-01      1.01427   1.264e-01
Objects:              5.300e+01      1.00000   5.300e+01
Flops:                1.142e+07      1.84544   8.696e+06  5.218e+07
Flops/sec:            9.044e+07      1.84355   6.874e+07  4.125e+08
MPI Messages:         1.125e+02      1.25000   1.042e+02  6.250e+02
MPI Message Lengths:  1.262e+05      1.30474   1.033e+03  6.456e+05
MPI Reductions:       7.900e+01      1.00000

Flop counting convention: 1 flop = 1 real number operation of type (multiply/divide/add/subtract)
                            e.g., VecAXPY() for real vectors of length N --> 2N flops
                            and VecAXPY() for complex vectors of length N --> 8N flops

Summary of Stages:   ----- Time ------  ----- Flops -----  --- Messages ---  -- Message Lengths --  -- Reductions --
                        Avg     %Total     Avg     %Total   counts   %Total     Avg         %Total   counts   %Total 
 0:      Main Stage: 1.2637e-01 100.0%  5.2177e+07 100.0%  6.250e+02 100.0%  1.033e+03      100.0%  6.300e+01  79.7% 

------------------------------------------------------------------------------------------------------------------------
See the 'Profiling' chapter of the users' manual for details on interpreting output.
Phase summary info:
   Count: number of times phase was executed
   Time and Flops: Max - maximum over all processors
                   Ratio - ratio of maximum to minimum over all processors
   Mess: number of messages sent
   Avg. len: average message length
   Reduct: number of global reductions
   Global: entire computation
   Stage: stages of a computation. Set stages with PetscLogStagePush() and PetscLogStagePop().
      %T - percent time in this phase         %F - percent flops in this phase
      %M - percent messages in this phase     %L - percent message lengths in this phase
      %R - percent reductions in this phase
   Total Mflop/s: 10e-6 * (sum of flops over all processors)/(max time over all processors)
------------------------------------------------------------------------------------------------------------------------
Event                Count      Time (sec)     Flops                             --- Global ---  --- Stage ---   Total
                   Max Ratio  Max     Ratio   Max  Ratio  Mess   Avg len Reduct  %T %F %M %L %R  %T %F %M %L %R Mflop/s
------------------------------------------------------------------------------------------------------------------------

--- Event Stage 0: Main Stage

VecMDot               14 1.0 4.9334e-0331.2 1.09e+05 1.6 0.0e+00 0.0e+00 1.4e+01  1  1  0  0 18   1  1  0  0 22   107
VecNorm               16 1.0 5.1327e-03 5.1 1.66e+04 1.6 0.0e+00 0.0e+00 1.6e+01  3  0  0  0 20   3  0  0  0 25    16
VecScale              15 1.0 7.4172e-0437.0 7.80e+03 1.6 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0    51
VecCopy                4 1.0 7.8678e-06 1.9 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
VecSet                20 1.0 1.8358e-05 3.0 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
VecAXPY                2 1.0 7.2620e-03 1.1 2.08e+03 1.6 0.0e+00 0.0e+00 0.0e+00  6  0  0  0  0   6  0  0  0  0     1
VecMAXPY              15 1.0 4.9591e-05 1.6 1.24e+05 1.6 0.0e+00 0.0e+00 0.0e+00  0  1  0  0  0   0  1  0  0  0 12104
VecAssemblyBegin       3 1.0 6.3801e-04 1.1 0.00e+00 0.0 2.6e+01 4.4e+02 9.0e+00  0  0  4  2 11   0  0  4  2 14     0
VecAssemblyEnd         3 1.0 3.0041e-05 1.2 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
VecScatterBegin       16 1.0 1.1063e-04 1.3 0.00e+00 0.0 4.5e+02 5.6e+02 0.0e+00  0  0 72 39  0   0  0 72 39  0     0
VecScatterEnd         16 1.0 1.2975e-02193.0 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  5  0  0  0  0   5  0  0  0  0     0
VecNormalize          15 1.0 5.2514e-0316.3 2.34e+04 1.6 0.0e+00 0.0e+00 1.5e+01  3  0  0  0 19   3  0  0  0 24    22
MatMult               15 1.0 1.3833e-0216.2 7.74e+05 1.7 4.2e+02 5.3e+02 0.0e+00  5  7 67 34  0   5  7 67 34  0   263
MatSolve              15 1.0 1.2808e-03 1.6 2.41e+06 1.7 0.0e+00 0.0e+00 0.0e+00  1 22  0  0  0   1 22  0  0  0  8881
MatLUFactorNum         1 1.0 6.0060e-03 2.1 8.10e+06 2.1 0.0e+00 0.0e+00 0.0e+00  4 69  0  0  0   4 69  0  0  0  5979
MatILUFactorSym        1 1.0 1.9248e-02 2.0 0.00e+00 0.0 0.0e+00 0.0e+00 1.0e+00 12  0  0  0  1  12  0  0  0  2     0
MatAssemblyBegin       2 1.0 1.8780e-0261.9 0.00e+00 0.0 3.9e+01 9.3e+03 4.0e+00 12  0  6 56  5  12  0  6 56  6     0
MatAssemblyEnd         2 1.0 1.1275e-02 1.0 0.00e+00 0.0 5.6e+01 1.3e+02 8.0e+00  9  0  9  1 10   9  0  9  1 13     0
MatGetRowIJ            1 1.0 6.9141e-06 3.6 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
MatGetOrdering         1 1.0 1.4782e-04 2.5 0.00e+00 0.0 0.0e+00 0.0e+00 4.0e+00  0  0  0  0  5   0  0  0  0  6     0
MatZeroEntries         3 1.0 1.4281e-04 1.9 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
KSPGMRESOrthog        14 1.0 4.9934e-0321.8 2.18e+05 1.6 0.0e+00 0.0e+00 1.4e+01  1  2  0  0 18   1  2  0  0 22   212
KSPSetup               2 1.0 8.2970e-05 1.5 0.00e+00 0.0 0.0e+00 0.0e+00 0.0e+00  0  0  0  0  0   0  0  0  0  0     0
KSPSolve               1 1.0 4.1033e-02 1.0 1.14e+07 1.8 4.2e+02 5.3e+02 3.5e+01 32100 67 34 44  32100 67 34 56  1272
PCSetUp                2 1.0 2.5765e-02 2.0 8.10e+06 2.1 0.0e+00 0.0e+00 5.0e+00 16 69  0  0  6  16 69  0  0  8  1394
PCSetUpOnBlocks        1 1.0 2.5468e-02 2.0 8.10e+06 2.1 0.0e+00 0.0e+00 5.0e+00 15 69  0  0  6  16 69  0  0  8  1410
PCApply               15 1.0 1.4620e-03 1.5 2.41e+06 1.7 0.0e+00 0.0e+00 0.0e+00  1 22  0  0  0   1 22  0  0  0  7780
------------------------------------------------------------------------------------------------------------------------

Memory usage is given in bytes:

Object Type          Creations   Destructions     Memory  Descendants' Mem.
Reports information only for process 0.

--- Event Stage 0: Main Stage

                 Vec    33             33       166816     0
         Vec Scatter     2              2         1736     0
           Index Set     9              9        17664     0
   IS L to G Mapping     1              1         4044     0
              Matrix     4              4      1308844     0
       Krylov Solver     2              2        18880     0
      Preconditioner     2              2         1408     0
========================================================================================================================
Average time to get PetscTime(): 0
Average time for MPI_Barrier(): 4.20094e-05
Average time for zero size MPI_Send(): 4.16438e-05
#PETSc Option Table entries:
-d 3
-ksp_right_pc
-log_summary
-n 6
-pc_type bjacobi
-sub_pc_factor_levels 4
-sub_pc_factor_zeropivot 0
-sub_pc_type ilu
#End of PETSc Option Table entries
Compiled without FORTRAN kernels
Compiled with full precision matrices (default)
sizeof(short) 2 sizeof(int) 4 sizeof(long) 8 sizeof(void*) 8 sizeof(PetscScalar) 8
Configure run at: Sat May 19 03:47:23 2012
Configure options: --with-debugging=false --COPTFLAGS=-O3 --CXXOPTFLAGS=-O3 --FOPTFLAGS=-O3 --with-clanguage=C++ --with-shared=1 --with-shared-libraries=1 --with-mpi-dir=/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid --with-mumps=true --download-mumps=1 --with-parmetis=true --download-parmetis=1 --with-superlu=true --download-superlu=1 --with-superludir=true --download-superlu_dist=1 --with-blacs=true --download-blacs=1 --with-scalapack=true --download-scalapack=1 --with-hypre=true --download-hypre=1 --with-blas-lib="[/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_intel_lp64.so,/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_sequential.so,/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_core.so]" --with-lapack-lib=/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t/libmkl_solver_lp64_sequential.a
-----------------------------------------
Libraries compiled on Sat May 19 03:47:23 CDT 2012 on daedalus 
Machine characteristics: Linux daedalus 2.6.32-34-generic #76-Ubuntu SMP Tue Aug 30 17:05:01 UTC 2011 x86_64 GNU/Linux 
Using PETSc directory: /org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5
Using PETSc arch: intel-11.1-lucid-mpich2-1.4.1-cxx-opt
-----------------------------------------
Using C compiler: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpicxx -O3   -fPIC   
Using Fortran compiler: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpif90 -fPIC -O3    
-----------------------------------------
Using include paths: -I/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/include -I/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/include -I/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/include -I/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/include  
------------------------------------------
Using C linker: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpicxx -O3 
Using Fortran linker: /org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/bin/mpif90 -fPIC -O3  
Using libraries: -Wl,-rpath,/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -L/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -lpetsc       -lX11 -Wl,-rpath,/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -L/org/centers/pecos/LIBRARIES/PETSC3/petsc-3.1-p5/intel-11.1-lucid-mpich2-1.4.1-cxx-opt/lib -lHYPRE -lcmumps -ldmumps -lsmumps -lzmumps -lmumps_common -lpord -lscalapack -lblacs -lsuperlu_dist_2.4 -lparmetis -lmetis -lsuperlu_4.0 -Wl,-rpath,/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t -L/org/centers/pecos/LIBRARIES/MKL/mkl-10.0.3.020-intel-11.1-lucid/lib/em64t -lmkl_solver_lp64_sequential -lmkl_intel_lp64 -lmkl_sequential -lmkl_core -ldl -Wl,-rpath,/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/lib -L/org/centers/pecos/LIBRARIES/MPICH2/mpich2-1.4.1-intel-11.1-lucid/lib -lmpich -lopa -lmpl -lrt -lpthread -Wl,-rpath,/opt/intel/Compiler/11.1/073/lib/intel64 -L/opt/intel/Compiler/11.1/073/lib/intel64 -Wl,-rpath,/usr/lib/gcc/x86_64-linux-gnu/4.4.3 -L/usr/lib/gcc/x86_64-linux-gnu/4.4.3 -limf -lsvml -lipgo -ldecimal -lgcc_s -lirc -lirc_s -lmpichf90 -lifport -lifcore -lm -lm -lmpichcxx -lstdc++ -lmpichcxx -lstdc++ -ldl -lmpich -lopa -lmpl -lrt -lpthread -limf -lsvml -lipgo -ldecimal -lgcc_s -lirc -lirc_s -ldl  
------------------------------------------
 ----------------------------------------------------------------------------------------------------------------
| libMesh Performance: Alive time=0.272737, Active time=0.120354                                                 |
 ----------------------------------------------------------------------------------------------------------------
| Event                              nCalls    Total Time  Avg Time    Total Time  Avg Time    % of Active Time  |
|                                              w/o Sub     w/o Sub     With Sub    With Sub    w/o S    With S   |
|----------------------------------------------------------------------------------------------------------------|
|                                                                                                                |
|                                                                                                                |
| DofMap                                                                                                         |
|   add_neighbors_to_send_list()     1         0.0001      0.000122    0.0002      0.000170    0.10     0.14     |
|   build_sparsity()                 1         0.0013      0.001284    0.0015      0.001480    1.07     1.23     |
|   create_dof_constraints()         1         0.0001      0.000055    0.0001      0.000055    0.05     0.05     |
|   distribute_dofs()                1         0.0005      0.000502    0.0095      0.009520    0.42     7.91     |
|   dof_indices()                    390       0.0003      0.000001    0.0003      0.000001    0.27     0.27     |
|   prepare_send_list()              1         0.0000      0.000044    0.0000      0.000044    0.04     0.04     |
|   reinit()                         1         0.0008      0.000750    0.0008      0.000750    0.62     0.62     |
|                                                                                                                |
| EquationSystems                                                                                                |
|   build_solution_vector()          1         0.0002      0.000214    0.0014      0.001351    0.18     1.12     |
|                                                                                                                |
| ExodusII_IO                                                                                                    |
|   write_nodal_data()               1         0.0021      0.002112    0.0021      0.002112    1.75     1.75     |
|                                                                                                                |
| FE                                                                                                             |
|   compute_shape_functions()        90        0.0020      0.000022    0.0020      0.000022    1.63     1.63     |
|   init_shape_functions()           55        0.0002      0.000003    0.0002      0.000003    0.13     0.13     |
|                                                                                                                |
| FEMap                                                                                                          |
|   compute_affine_map()             90        0.0005      0.000005    0.0005      0.000005    0.38     0.38     |
|   compute_face_map()               54        0.0002      0.000004    0.0002      0.000004    0.16     0.16     |
|   init_face_shape_functions()      1         0.0000      0.000026    0.0000      0.000026    0.02     0.02     |
|   init_reference_to_physical_map() 55        0.0032      0.000058    0.0032      0.000058    2.63     2.63     |
|                                                                                                                |
| Mesh                                                                                                           |
|   find_neighbors()                 1         0.0004      0.000353    0.0044      0.004416    0.29     3.67     |
|   renumber_nodes_and_elem()        2         0.0001      0.000067    0.0001      0.000067    0.11     0.11     |
|                                                                                                                |
| MeshCommunication                                                                                              |
|   compute_hilbert_indices()        2         0.0008      0.000405    0.0008      0.000405    0.67     0.67     |
|   find_global_indices()            2         0.0001      0.000068    0.0069      0.003457    0.11     5.75     |
|   parallel_sort()                  2         0.0016      0.000818    0.0051      0.002543    1.36     4.23     |
|                                                                                                                |
| MeshOutput                                                                                                     |
|   write_equation_systems()         1         0.0000      0.000030    0.0035      0.003494    0.02     2.90     |
|                                                                                                                |
| MeshTools::Generation                                                                                          |
|   build_cube()                     1         0.0004      0.000446    0.0004      0.000446    0.37     0.37     |
|                                                                                                                |
| MetisPartitioner                                                                                               |
|   partition()                      1         0.0010      0.000977    0.0036      0.003595    0.81     2.99     |
|                                                                                                                |
| Parallel                                                                                                       |
|   allgather()                      9         0.0056      0.000624    0.0056      0.000624    4.67     4.67     |
|   broadcast()                      2         0.0000      0.000007    0.0000      0.000007    0.01     0.01     |
|   gather()                         2         0.0000      0.000004    0.0000      0.000004    0.01     0.01     |
|   max(scalar)                      2         0.0049      0.002456    0.0049      0.002456    4.08     4.08     |
|   max(vector)                      2         0.0004      0.000197    0.0004      0.000197    0.33     0.33     |
|   min(vector)                      2         0.0009      0.000463    0.0009      0.000463    0.77     0.77     |
|   probe()                          50        0.0072      0.000145    0.0072      0.000145    6.02     6.02     |
|   receive()                        50        0.0001      0.000002    0.0073      0.000147    0.08     6.10     |
|   send()                           50        0.0001      0.000002    0.0001      0.000002    0.06     0.06     |
|   send_receive()                   54        0.0001      0.000002    0.0076      0.000140    0.09     6.29     |
|   sum()                            12        0.0048      0.000398    0.0048      0.000398    3.97     3.97     |
|                                                                                                                |
| Parallel::Request                                                                                              |
|   wait()                           50        0.0000      0.000001    0.0000      0.000001    0.03     0.03     |
|                                                                                                                |
| Partitioner                                                                                                    |
|   set_node_processor_ids()         1         0.0002      0.000166    0.0034      0.003376    0.14     2.81     |
|   set_parent_processor_ids()       1         0.0000      0.000016    0.0000      0.000016    0.01     0.01     |
|                                                                                                                |
| PetscLinearSolver                                                                                              |
|   solve()                          1         0.0723      0.072327    0.0723      0.072327    60.10    60.10    |
|                                                                                                                |
| System                                                                                                         |
|   assemble()                       1         0.0078      0.007750    0.0138      0.013801    6.44     11.47    |
 ----------------------------------------------------------------------------------------------------------------
| Totals:                            1044      0.1204                                          100.00            |
 ----------------------------------------------------------------------------------------------------------------

 
***************************************************************
* Done Running Example  mpirun -np 6 ./subdomains_ex2-opt -pc_type bjacobi -sub_pc_type ilu -sub_pc_factor_levels 4 -sub_pc_factor_zeropivot 0 -ksp_right_pc -log_summary
***************************************************************
</pre>
</div>
<?php make_footer() ?>
</body>
</html>
<?php if (0) { ?>
\#Local Variables:
\#mode: html
\#End:
<?php } ?>

/* The Next Great Finite Element Library. */
/* Copyright (C) 2003  Benjamin S. Kirk */

/* This library is free software; you can redistribute it and/or */
/* modify it under the terms of the GNU Lesser General Public */
/* License as published by the Free Software Foundation; either */
/* version 2.1 of the License, or (at your option) any later version. */

/* This library is distributed in the hope that it will be useful, */
/* but WITHOUT ANY WARRANTY; without even the implied warranty of */
/* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU */
/* Lesser General Public License for more details. */

/* You should have received a copy of the GNU Lesser General Public */
/* License along with this library; if not, write to the Free Software */
/* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA */




 // <h1>Miscellaneous Example 1 - Infinite Elements for the Wave Equation</h1>
 //
 // This is the sixth example program.  It builds on
 // the previous examples, and introduces the Infinite
 // Element class.  Note that the library must be compiled
 // with Infinite Elements enabled.  Otherwise, this
 // example will abort.
 // This example intends to demonstrate the similarities
 // between the \p FE and the \p InfFE classes in libMesh.
 // The matrices are assembled according to the wave equation.
 // However, for practical applications a time integration
 // scheme (as introduced in subsequent examples) should be
 // used.

// C++ include files that we need
#include <iostream>
#include <algorithm>
#include <math.h>

// Basic include file needed for the mesh functionality.
#include "libmesh/exodusII_io.h"
#include "libmesh/libmesh.h"
#include "libmesh/serial_mesh.h"
#include "libmesh/mesh_generation.h"
#include "libmesh/linear_implicit_system.h"
#include "libmesh/equation_systems.h"

// Define the Finite and Infinite Element object.
#include "libmesh/fe.h"
#include "libmesh/inf_fe.h"
#include "libmesh/inf_elem_builder.h"

// Define Gauss quadrature rules.
#include "libmesh/quadrature_gauss.h"

// Define useful datatypes for finite element
// matrix and vector components.
#include "libmesh/sparse_matrix.h"
#include "libmesh/numeric_vector.h"
#include "libmesh/dense_matrix.h"
#include "libmesh/dense_vector.h"

// Define the DofMap, which handles degree of freedom
// indexing.
#include "libmesh/dof_map.h"

// The definition of a vertex associated with a Mesh.
#include "libmesh/node.h"

// The definition of a geometric element
#include "libmesh/elem.h"

// Bring in everything from the libMesh namespace
using namespace libMesh;

// Function prototype.  This is similar to the Poisson
// assemble function of example 4.  
void assemble_wave (EquationSystems& es,
                    const std::string& system_name);

// Begin the main program.
int main (int argc, char** argv)
{
  // Initialize libMesh, like in example 2.
  LibMeshInit init (argc, argv);
  
  // This example requires Infinite Elements   
#ifndef LIBMESH_ENABLE_INFINITE_ELEMENTS
  libmesh_example_assert(false, "--enable-ifem");
#else
  
  // Skip this 3D example if libMesh was compiled as 1D/2D-only.
  libmesh_example_assert(3 <= LIBMESH_DIM, "3D support");
  
  // Tell the user what we are doing.
  std::cout << "Running ex6 with dim = 3" << std::endl << std::endl;        
  
  // Create a serialized mesh.
  // InfElemBuilder still requires some updates to be ParallelMesh
  // compatible
  
  SerialMesh mesh;

  // Use the internal mesh generator to create elements
  // on the square [-1,1]^3, of type Hex8.
  MeshTools::Generation::build_cube (mesh,
                                     4, 4, 4,
                                     -1., 1.,
                                     -1., 1.,
                                     -1., 1.,
                                     HEX8);
  
  // Print information about the mesh to the screen.
  mesh.print_info();

  // Write the mesh before the infinite elements are added
#ifdef LIBMESH_HAVE_EXODUS_API
  ExodusII_IO(mesh).write ("orig_mesh.e");
#endif

  // Normally, when a mesh is imported or created in
  // libMesh, only conventional elements exist.  The infinite
  // elements used here, however, require prescribed
  // nodal locations (with specified distances from an imaginary
  // origin) and configurations that a conventional mesh creator 
  // in general does not offer.  Therefore, an efficient method
  // for building infinite elements is offered.  It can account
  // for symmetry planes and creates infinite elements in a fully
  // automatic way.
  //
  // Right now, the simplified interface is used, automatically
  // determining the origin.  Check \p MeshBase for a generalized
  // method that can even return the element faces of interior
  // vibrating surfaces.  The \p bool determines whether to be 
  // verbose.
  InfElemBuilder builder(mesh);
  builder.build_inf_elem(true);

  // Print information about the mesh to the screen.
  mesh.print_info();

  // Write the mesh with the infinite elements added.
  // Compare this to the original mesh.
#ifdef LIBMESH_HAVE_EXODUS_API
  ExodusII_IO(mesh).write ("ifems_added.e");
#endif

  // After building infinite elements, we have to let 
  // the elements find their neighbors again.
  mesh.find_neighbors();
  
  // Create an equation systems object, where \p ThinSystem
  // offers only the crucial functionality for solving a 
  // system.  Use \p ThinSystem when you want the sleekest
  // system possible.
  EquationSystems equation_systems (mesh);
  
  // Declare the system and its variables.
  // Create a system named "Wave".  This can
  // be a simple, steady system
  equation_systems.add_system<LinearImplicitSystem> ("Wave");
        
  // Create an FEType describing the approximation
  // characteristics of the InfFE object.  Note that
  // the constructor automatically defaults to some
  // sensible values.  But use \p FIRST order 
  // approximation.
  FEType fe_type(FIRST);
  
  // Add the variable "p" to "Wave".  Note that there exist
  // various approaches in adding variables.  In example 3, 
  // \p add_variable took the order of approximation and used
  // default values for the \p FEFamily, while here the \p FEType 
  // is used.
  equation_systems.get_system("Wave").add_variable("p", fe_type);
  
  // Give the system a pointer to the matrix assembly
  // function.
  equation_systems.get_system("Wave").attach_assemble_function (assemble_wave);
  
  // Set the speed of sound and fluid density
  // as \p EquationSystems parameter,
  // so that \p assemble_wave() can access it.
  equation_systems.parameters.set<Real>("speed")          = 1.;
  equation_systems.parameters.set<Real>("fluid density")  = 1.;
  
  // Initialize the data structures for the equation system.
  equation_systems.init();
  
  // Prints information about the system to the screen.
  equation_systems.print_info();

  // Solve the system "Wave".
  equation_systems.get_system("Wave").solve();
  
  // Write the whole EquationSystems object to file.
  // For infinite elements, the concept of nodal_soln()
  // is not applicable. Therefore, writing the mesh in
  // some format @e always gives all-zero results at
  // the nodes of the infinite elements.  Instead,
  // use the FEInterface::compute_data() methods to
  // determine physically correct results within an
  // infinite element.
  equation_systems.write ("eqn_sys.dat", libMeshEnums::WRITE);
  
  // All done.  
  return 0;

#endif // else part of ifndef LIBMESH_ENABLE_INFINITE_ELEMENTS
}

// This function assembles the system matrix and right-hand-side
// for the discrete form of our wave equation.
void assemble_wave(EquationSystems& es,
                   const std::string& system_name)
{
  // It is a good idea to make sure we are assembling
  // the proper system.
  libmesh_assert_equal_to (system_name, "Wave");


#ifdef LIBMESH_ENABLE_INFINITE_ELEMENTS
  
  // Get a constant reference to the mesh object.
  const MeshBase& mesh = es.get_mesh();

  // Get a reference to the system we are solving.
  LinearImplicitSystem & system = es.get_system<LinearImplicitSystem>("Wave");
  
  // A reference to the \p DofMap object for this system.  The \p DofMap
  // object handles the index translation from node and element numbers
  // to degree of freedom numbers.
  const DofMap& dof_map = system.get_dof_map();
  
  // The dimension that we are running.
  const unsigned int dim = mesh.mesh_dimension();
  
  // Copy the speed of sound to a local variable.
  const Real speed = es.parameters.get<Real>("speed");
  
  // Get a constant reference to the Finite Element type
  // for the first (and only) variable in the system.
  const FEType& fe_type = dof_map.variable_type(0);
  
  // Build a Finite Element object of the specified type.  Since the
  // \p FEBase::build() member dynamically creates memory we will
  // store the object as an \p AutoPtr<FEBase>.  Check ex5 for details.
  AutoPtr<FEBase> fe (FEBase::build(dim, fe_type));
  
  // Do the same for an infinite element.
  AutoPtr<FEBase> inf_fe (FEBase::build_InfFE(dim, fe_type));
  
  // A 2nd order Gauss quadrature rule for numerical integration.
  QGauss qrule (dim, SECOND);
  
  // Tell the finite element object to use our quadrature rule.   
  fe->attach_quadrature_rule (&qrule);
  
  // Due to its internal structure, the infinite element handles 
  // quadrature rules differently.  It takes the quadrature
  // rule which has been initialized for the FE object, but
  // creates suitable quadrature rules by @e itself.  The user
  // need not worry about this.   
  inf_fe->attach_quadrature_rule (&qrule);
  
  // Define data structures to contain the element matrix
  // and right-hand-side vector contribution.  Following
  // basic finite element terminology we will denote these
  // "Ke",  "Ce", "Me", and "Fe" for the stiffness, damping
  // and mass matrices, and the load vector.  Note that in 
  // Acoustics, these descriptors though do @e not match the 
  // true physical meaning of the projectors.  The final 
  // overall system, however, resembles the conventional 
  // notation again.   
  DenseMatrix<Number> Ke;
  DenseMatrix<Number> Ce;
  DenseMatrix<Number> Me;
  DenseVector<Number> Fe;
  
  // This vector will hold the degree of freedom indices for
  // the element.  These define where in the global system
  // the element degrees of freedom get mapped.   
  std::vector<unsigned int> dof_indices;
  
  // Now we will loop over all the elements in the mesh.
  // We will compute the element matrix and right-hand-side
  // contribution.
  MeshBase::const_element_iterator           el = mesh.active_local_elements_begin();
  const MeshBase::const_element_iterator end_el = mesh.active_local_elements_end();
  
  for ( ; el != end_el; ++el)
    {      
      // Store a pointer to the element we are currently
      // working on.  This allows for nicer syntax later.       
      const Elem* elem = *el;
      
      // Get the degree of freedom indices for the
      // current element.  These define where in the global
      // matrix and right-hand-side this element will
      // contribute to.       
      dof_map.dof_indices (elem, dof_indices);

      
      // The mesh contains both finite and infinite elements.  These
      // elements are handled through different classes, namely
      // \p FE and \p InfFE, respectively.  However, since both
      // are derived from \p FEBase, they share the same interface,
      // and overall burden of coding is @e greatly reduced through
      // using a pointer, which is adjusted appropriately to the
      // current element type.       
      FEBase* cfe=NULL;
      
      // This here is almost the only place where we need to
      // distinguish between finite and infinite elements.
      // For faster computation, however, different approaches
      // may be feasible.
      //
      // Up to now, we do not know what kind of element we
      // have.  Aske the element of what type it is:        
      if (elem->infinite())
        {           
          // We have an infinite element.  Let \p cfe point
          // to our \p InfFE object.  This is handled through
          // an AutoPtr.  Through the \p AutoPtr::get() we "borrow"
          // the pointer, while the \p  AutoPtr \p inf_fe is
          // still in charge of memory management.           
          cfe = inf_fe.get(); 
        }
      else
        {
          // This is a conventional finite element.  Let \p fe handle it.           
            cfe = fe.get();
          
          // Boundary conditions.
          // Here we just zero the rhs-vector. For natural boundary 
          // conditions check e.g. previous examples.           
          {              
            // Zero the RHS for this element.                
            Fe.resize (dof_indices.size());
            
            system.rhs->add_vector (Fe, dof_indices);
          } // end boundary condition section             
        } // else ( if (elem->infinite())) )

      // This is slightly different from the Poisson solver:
      // Since the finite element object may change, we have to
      // initialize the constant references to the data fields
      // each time again, when a new element is processed.
      //
      // The element Jacobian * quadrature weight at each integration point.          
      const std::vector<Real>& JxW = cfe->get_JxW();
      
      // The element shape functions evaluated at the quadrature points.       
      const std::vector<std::vector<Real> >& phi = cfe->get_phi();
      
      // The element shape function gradients evaluated at the quadrature
      // points.       
      const std::vector<std::vector<RealGradient> >& dphi = cfe->get_dphi();

      // The infinite elements need more data fields than conventional FE.  
      // These are the gradients of the phase term \p dphase, an additional 
      // radial weight for the test functions \p Sobolev_weight, and its
      // gradient.
      // 
      // Note that these data fields are also initialized appropriately by
      // the \p FE method, so that the weak form (below) is valid for @e both
      // finite and infinite elements.       
      const std::vector<RealGradient>& dphase  = cfe->get_dphase();
      const std::vector<Real>&         weight  = cfe->get_Sobolev_weight();
      const std::vector<RealGradient>& dweight = cfe->get_Sobolev_dweight();

      // Now this is all independent of whether we use an \p FE
      // or an \p InfFE.  Nice, hm? ;-)
      //
      // Compute the element-specific data, as described
      // in previous examples.       
      cfe->reinit (elem);
      
      // Zero the element matrices.  Boundary conditions were already
      // processed in the \p FE-only section, see above.       
      Ke.resize (dof_indices.size(), dof_indices.size());
      Ce.resize (dof_indices.size(), dof_indices.size());
      Me.resize (dof_indices.size(), dof_indices.size());
      
      // The total number of quadrature points for infinite elements
      // @e has to be determined in a different way, compared to
      // conventional finite elements.  This type of access is also
      // valid for finite elements, so this can safely be used
      // anytime, instead of asking the quadrature rule, as
      // seen in previous examples.       
      unsigned int max_qp = cfe->n_quadrature_points();
      
      // Loop over the quadrature points.        
      for (unsigned int qp=0; qp<max_qp; qp++)
        {          
          // Similar to the modified access to the number of quadrature 
          // points, the number of shape functions may also be obtained
          // in a different manner.  This offers the great advantage
          // of being valid for both finite and infinite elements.           
          const unsigned int n_sf = cfe->n_shape_functions();

          // Now we will build the element matrices.  Since the infinite
          // elements are based on a Petrov-Galerkin scheme, the
          // resulting system matrices are non-symmetric. The additional
          // weight, described before, is part of the trial space.
          //
          // For the finite elements, though, these matrices are symmetric
          // just as we know them, since the additional fields \p dphase,
          // \p weight, and \p dweight are initialized appropriately.
          //
          // test functions:    weight[qp]*phi[i][qp]
          // trial functions:   phi[j][qp]
          // phase term:        phase[qp]
          // 
          // derivatives are similar, but note that these are of type
          // Point, not of type Real.           
          for (unsigned int i=0; i<n_sf; i++)
            for (unsigned int j=0; j<n_sf; j++)
              {
                //         (ndt*Ht + nHt*d) * nH 
                Ke(i,j) +=
                  (                            //    (                         
                   (                           //      (                       
                    dweight[qp] * phi[i][qp]   //        Point * Real  = Point 
                    +                          //        +                     
                    dphi[i][qp] * weight[qp]   //        Point * Real  = Point 
                    ) * dphi[j][qp]            //      )       * Point = Real  
                   ) * JxW[qp];                //    )         * Real  = Real  

                // (d*Ht*nmut*nH - ndt*nmu*Ht*H - d*nHt*nmu*H)
                Ce(i,j) +=
                  (                                //    (                         
                   (dphase[qp] * dphi[j][qp])      //      (Point * Point) = Real  
                   * weight[qp] * phi[i][qp]       //      * Real * Real   = Real  
                   -                               //      -                       
                   (dweight[qp] * dphase[qp])      //      (Point * Point) = Real  
                   * phi[i][qp] * phi[j][qp]       //      * Real * Real   = Real  
                   -                               //      -                       
                   (dphi[i][qp] * dphase[qp])      //      (Point * Point) = Real  
                   * weight[qp] * phi[j][qp]       //      * Real * Real   = Real  
                   ) * JxW[qp];                    //    )         * Real  = Real  
                
                // (d*Ht*H * (1 - nmut*nmu))
                Me(i,j) +=
                  (                                       //    (                                  
                   (1. - (dphase[qp] * dphase[qp]))       //      (Real  - (Point * Point)) = Real 
                   * phi[i][qp] * phi[j][qp] * weight[qp] //      * Real *  Real  * Real    = Real 
                   ) * JxW[qp];                           //    ) * Real                    = Real 

              } // end of the matrix summation loop
        } // end of quadrature point loop

      // The element matrices are now built for this element.  
      // Collect them in Ke, and then add them to the global matrix.  
      // The \p SparseMatrix::add_matrix() member does this for us.
      Ke.add(1./speed        , Ce);
      Ke.add(1./(speed*speed), Me);

      // If this assembly program were to be used on an adaptive mesh,
      // we would have to apply any hanging node constraint equations
      dof_map.constrain_element_matrix(Ke, dof_indices);

      system.matrix->add_matrix (Ke, dof_indices);
    } // end of element loop

  // Note that we have not applied any boundary conditions so far.
  // Here we apply a unit load at the node located at (0,0,0).
  {
    // Iterate over local nodes
    MeshBase::const_node_iterator           nd = mesh.local_nodes_begin();
    const MeshBase::const_node_iterator nd_end = mesh.local_nodes_end();
    
    for (; nd != nd_end; ++nd)
      {        
        // Get a reference to the current node.
        const Node& node = **nd;
        
        // Check the location of the current node.
        if (fabs(node(0)) < TOLERANCE &&
            fabs(node(1)) < TOLERANCE &&
            fabs(node(2)) < TOLERANCE)
          {
            // The global number of the respective degree of freedom.
            unsigned int dn = node.dof_number(0,0,0);

            system.rhs->add (dn, 1.);
          }
      }
  }

#else

  // dummy assert 
  libmesh_assert_not_equal_to (es.get_mesh().mesh_dimension(), 1);

#endif //ifdef LIBMESH_ENABLE_INFINITE_ELEMENTS
  
  // All done!   
  return;
}


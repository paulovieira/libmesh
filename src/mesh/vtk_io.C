// The libMesh Finite Element Library.
// Copyright (C) 2002-2012 Benjamin S. Kirk, John W. Peterson, Roy H. Stogner

// This library is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public
// License as published by the Free Software Foundation; either
// version 2.1 of the License, or (at your option) any later version.

// This library is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
// Lesser General Public License for more details.

// You should have received a copy of the GNU Lesser General Public
// License along with this library; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


// C++ includes
#include <fstream>

// Local includes
#include "libmesh/libmesh_config.h"
#include "libmesh/vtk_io.h"
#include "libmesh/mesh_base.h"
#include "libmesh/equation_systems.h"
#include "libmesh/cell_tet4.h"
#include "libmesh/cell_tet10.h"
#include "libmesh/cell_prism6.h"
#include "libmesh/cell_pyramid5.h"
#include "libmesh/cell_hex8.h"
#include "libmesh/cell_hex20.h"
#include "libmesh/numeric_vector.h"
#include "libmesh/system.h"
//#include "libmesh/cell_inf.h"
//#include "libmesh/cell_inf_prism12.h"
//#include "libmesh/cell_prism6.h"
//#include "libmesh/cell_tet4.h"

//#include "libmesh/cell_inf_hex16.h"
//#include "libmesh/cell_inf_prism6.h"
//#include "libmesh/cell_prism.h"
//#include "libmesh/cell_tet.h"

//#include "libmesh/cell_hex27.h"
//#include "libmesh/cell_inf_hex18.h"
//#include "libmesh/cell_inf_prism.h"
//#include "libmesh/cell_pyramid5.h"

//#include "libmesh/cell_hex8.h"
//#include "libmesh/cell_inf_hex8.h"
//#include "libmesh/cell_prism15.h"
//#include "libmesh/cell_pyramid.h"

//#include "libmesh/cell_hex.h"
//#include "libmesh/cell_inf_hex.h"
//#include "libmesh/cell_prism18.h"
//#include "libmesh/cell_tet10.h"



#include "libmesh/mesh_data.h"

#ifdef LIBMESH_HAVE_VTK

#include "libmesh/ignore_warnings.h"
#include "vtkXMLUnstructuredGridReader.h"
#include "vtkXMLUnstructuredGridWriter.h"
#include "vtkXMLPUnstructuredGridWriter.h"
#include "vtkUnstructuredGrid.h"
#include "vtkGenericGeometryFilter.h"
#include "vtkCellArray.h"
#include "vtkConfigure.h"
/*
#include "vtkTetra.h"
#include "vtkTriangle.h"
#include "vtkWedge.h"
#include "vtkPyramid.h"
#include "vtkQuad.h"
#include "vtkHexahedron.h"
#include "vtkQuadraticTriangle.h"
#include "vtkQuadraticQuad.h"
#include "vtkQuadraticHexahedron.h"
#include "vtkQuadraticWedge.h"
#include "vtkQuadraticTetra.h"
#include "vtkBiQuadraticQuad.h"
*/
//#include "vtkFloatArray.h"
#include "vtkDoubleArray.h"
#include "vtkPointData.h"
#include "vtkPoints.h"
#include "libmesh/restore_warnings.h"
#endif //LIBMESH_HAVE_VTK

namespace libMesh
{

//static unsigned int vtk_tet4_mapping[4]= {0,1,2,3};
//static unsigned int vtk_pyramid5_mapping[5]= {0,3,2,1,4};
//static unsigned int vtk_wedge6_mapping[6]= {0,2,1,3,5,4};
//static unsigned int vtk_hex8_mapping[8]= {0,1,2,3,4,5,6,7};

#ifdef LIBMESH_HAVE_VTK // private functions
vtkPoints* VTKIO::nodes_to_vtk(const MeshBase& mesh){
	if (libMesh::processor_id() == 0)
		{

			vtkPoints* points = vtkPoints::New();
			vtkDoubleArray* pcoords = vtkDoubleArray::New();
			pcoords->SetNumberOfComponents(LIBMESH_DIM);
			pcoords->SetNumberOfTuples(mesh.n_nodes());

			// FIXME change to local iterators when I've figured out how to write in parallel
			//  MeshBase::const_node_iterator nd = mesh.local_nodes_begin();
			//  MeshBase::const_node_iterator nd_end = mesh.local_nodes_end();
			//  points->SetNumberOfPoints(mesh.n_local_nodes()); // it seems that it needs this to prevent a segfault
			MeshBase::const_node_iterator nd = mesh.nodes_begin();
			MeshBase::const_node_iterator nd_end = mesh.nodes_end();
			// write out nodal data
			for (;nd!=nd_end;++nd)
			{
				const Node* node = (*nd);
				float pnt[LIBMESH_DIM];
				for(unsigned int i=0;i<LIBMESH_DIM;++i){
					pnt[i] = (*node)(i);
				}
				//     pcoords->InsertNextTuple(pnt);
				pcoords->SetTuple(node->id(),pnt);
				//     libMesh::out<<"pnt "<<pnt[0]<<" "<<pnt[1]<<" "<<pcoords-><<std::endl;
				//     points->InsertPoint(node->id(),pnt);
				//     libMesh::out<<"point "<<node->id()<<" "<<(*node)(0)<<" "<<(*node)(1)<<std::endl;
			}
			points->SetData(pcoords);
			pcoords->Delete();
			return points;
	}
	return NULL;
}

vtkCellArray* VTKIO::cells_to_vtk(const MeshBase& mesh, std::vector<int>& types){
	if (libMesh::processor_id() == 0)
	{

		vtkCellArray* cells = vtkCellArray::New();
		//     cells->Allocate(100000);
		vtkIdList *pts = vtkIdList::New();

		MeshBase::const_element_iterator       it  = mesh.active_elements_begin();
		const MeshBase::const_element_iterator end = mesh.active_elements_end();

                int active_element_counter = 0;

		for ( ; it != end; ++it, ++active_element_counter) {
			const Elem *elem  = *it;

			vtkIdType celltype = VTK_EMPTY_CELL; // initialize to something to avoid compiler warning

			switch(elem->type())
			{
				case EDGE2:
					celltype = VTK_LINE;
					break;
				case EDGE3:
					celltype = VTK_QUADRATIC_EDGE;
					break;// 1
				case TRI3:
					celltype = VTK_TRIANGLE;
					break;// 3
				case TRI6:
					celltype = VTK_QUADRATIC_TRIANGLE;
					break;// 4
				case QUAD4:
					celltype = VTK_QUAD;
					break;// 5
				case QUAD8:
					celltype = VTK_QUADRATIC_QUAD;
					break;// 6
				case TET4:
					celltype = VTK_TETRA;
					break;// 8
				case TET10:
					celltype = VTK_QUADRATIC_TETRA;
					break;// 9
				case HEX8:
					celltype = VTK_HEXAHEDRON;
					break;// 10
				case HEX20:
					celltype = VTK_QUADRATIC_HEXAHEDRON;
					break;// 12
				case PRISM6:
					celltype = VTK_WEDGE;
					break;// 13
				case PRISM15:
					celltype = VTK_QUADRATIC_WEDGE;
					break;// 14
				case PRISM18:
				  celltype = VTK_BIQUADRATIC_QUADRATIC_WEDGE;
				  break;
				case HEX27:
				  celltype = VTK_TRIQUADRATIC_HEXAHEDRON;
				  break;
				case PYRAMID5:
					celltype = VTK_PYRAMID;
					break;// 16
#if VTK_MAJOR_VERSION > 5 || (VTK_MAJOR_VERSION == 5 && VTK_MINOR_VERSION > 0)
				case QUAD9:
					celltype = VTK_BIQUADRATIC_QUAD;
					break;
#else
				case QUAD9:
#endif
				case EDGE4:
				case INFEDGE2:
				case INFQUAD4:
				case INFQUAD6:
				case INFHEX8:
				case INFHEX16:
				case INFHEX18:
				case INFPRISM6:
				case INFPRISM12:
				case NODEELEM:
				case INVALID_ELEM:
				default:
					{
						libMesh::err<<"element type "<<elem->type()<<" not implemented"<<std::endl;
						libmesh_error();
					}
			}
			//        libMesh::out<<"elem "<<elem->n_nodes()<<" "<<elem->n_sub_elem()<<std::endl;
			//        libMesh::out<<"type "<<celltype<<" "<<VTK_BIQUADRATIC_QUAD<<std::endl;
			types[active_element_counter] = celltype;
			pts->SetNumberOfIds(elem->n_nodes());
			// get the connectivity for this element
			std::vector<unsigned int> conn;
			elem->connectivity(0,VTK,conn);
			//        libMesh::out<<"conn size "<<conn.size()<<std::endl;
			for(unsigned int i=0;i<conn.size();++i)
			{
				//           libMesh::out<<" sz "<<pts->GetNumberOfIds()<<std::endl;
				pts->InsertId(i,conn[i]);
			}
			//        libMesh::out<<"set cell"<<std::endl;
			cells->InsertNextCell(pts);
			//         }
			//         libMesh::out<<"finish set cell"<<std::endl;
		} // end loop over active elements
		pts->Delete();

		return cells;
	}
	return NULL;
}

/*
 * single processor implementation, this can be done in parallel, but requires a
 * bit more thinking on how to deal with overlapping local solution vectors
 */
/*
void VTKIO::solution_to_vtk(const EquationSystems& es,vtkUnstructuredGrid*& grid){
//   if (libMesh::processor_id() == 0)
//      {
//   std::vector<Number> soln;
//   libMesh::out<<"build"<<std::endl;
//   es.build_solution_vector(soln);
//   libMesh::out<<"add solution "<<soln.size()<<std::endl;

	if(libMesh::processor_id()==0){
		const MeshBase& mesh = (MeshBase&)es.get_mesh();
		const unsigned int n_nodes = es.get_mesh().n_nodes();
		const unsigned int n_vars = es.n_vars();
			// write the solutions belonging to the system
			for(unsigned int i=0;i<es.n_systems();++i){ // for all systems, regardless of whether they are active or not
				for(unsigned int j=0;j<n_vars;++j){
					const System& sys = es.get_system(i);
					const std::string& varname = sys.variable_name(j);
					vtkFloatArray *data = vtkFloatArray::New();
					data->SetName(varname.c_str());
					data->SetNumberOfValues(n_nodes);
					MeshBase::const_node_iterator it = mesh.nodes_begin();
					const MeshBase::const_node_iterator n_end = mesh.nodes_end();
					for(;it!=n_end;++it){
						if((*it)->n_comp(i,j)>0){
							const unsigned int nd_id = (*it)->id();
	//                  const unsigned int dof_nr = (*it)->dof_number(i,j,0);
	//                  data->InsertValue((*it)->id(),sys.current_solution(count++));
							data->InsertValue(nd_id,libmesh_real(13.0));
//                     soln[nd_id*n_vars+j]));
	//               data->InsertValue((*it)->id(),sys.current_solution(dof_nr));
						}else{
							data->InsertValue((*it)->id(),0);
						}
					}
					grid->GetPointData()->AddArray(data);
				}
			}
   }
}*/

void VTKIO::solution_to_vtk(const EquationSystems& es, vtkUnstructuredGrid*& grid){

        const MeshBase& mesh = es.get_mesh();
        const unsigned int n_nodes = mesh.n_nodes();

	// loop over the systems
	for(unsigned int i=0;i<es.n_systems();++i){ // for all systems, regardless of whether they are active or not

                const System& sys = es.get_system(i);
                std::vector<Number> soln;
                sys.solution->localize_to_one(soln);

                // write only on one processor
                if(libMesh::processor_id()==0){

			const unsigned int n_vars = sys.n_vars();
			// loop over variables
			for(unsigned int j=0;j<n_vars;++j){

				std::string name = sys.variable_name(j);

				vtkDoubleArray *data = vtkDoubleArray::New();

				data->SetName(name.c_str());

				data->SetNumberOfValues(n_nodes);

				for(unsigned int k=0;k<n_nodes;++k){

					const unsigned int dof_nr = mesh.node(k).dof_number(i,j,0);

#ifdef LIBMESH_USE_COMPLEX_NUMBERS
					data->SetValue(k,soln[dof_nr].real());
#else
					data->SetValue(k,soln[dof_nr]);
#endif

				}
				grid->GetPointData()->AddArray(data);
				data->Delete();
			}

		}
	}
}

/*
 * FIXME now this is known to write nonsense on AMR meshes
 * and it strips the imaginary parts of complex Numbers
 */
void VTKIO::system_vectors_to_vtk(const EquationSystems& es,vtkUnstructuredGrid*& grid){
	if (libMesh::processor_id() == 0){

		std::map<std::string,std::vector<Number> > vecs;
		for(unsigned int i=0;i<es.n_systems();++i){
			const System& sys = es.get_system(i);
			System::const_vectors_iterator v_end = sys.vectors_end();
			System::const_vectors_iterator it = sys.vectors_begin();
			for(;it!= v_end;++it){ // for all vectors on this system
				std::vector<Number> values;
//				libMesh::out<<"it "<<it->first<<std::endl;

				it->second->localize_to_one(values,0);
//				libMesh::out<<"finish localize"<<std::endl;
				vecs[it->first] = values;
			}
		}


		std::map<std::string,std::vector<Number> >::iterator it = vecs.begin();

		for(;it!=vecs.end();++it){

			vtkDoubleArray *data = vtkDoubleArray::New();

			data->SetName(it->first.c_str());

			libmesh_assert_equal_to (it->second.size(), es.get_mesh().n_nodes());

			data->SetNumberOfValues(it->second.size());

			for(unsigned int i=0;i<it->second.size();++i){

#ifdef LIBMESH_USE_COMPLEX_NUMBERS
				data->SetValue(i,it->second[i].real());
#else
				data->SetValue(i,it->second[i]);
#endif

			}

			grid->GetPointData()->AddArray(data);
			data->Delete();
		}

	}

/*
	// write out the vectors added to the systems
	if (libMesh::processor_id() == 0)
		{
			libMesh::out<<"write system vectors"<<std::endl;
		const MeshBase& mesh = (MeshBase&)es.get_mesh();
		const unsigned int n_nodes = mesh.n_nodes();
		for(unsigned int i=0;i<es.n_systems();++i){ // for all systems, regardless of whether they are active or not
			const System& sys = es.get_system(i);
			libMesh::out<<"i "<<i<<std::endl;
			System::const_vectors_iterator v_end = sys.vectors_end();
			System::const_vectors_iterator it = sys.vectors_begin();
			for(;it!= v_end;++it){ // for all vectors on this system
				libMesh::out<<"it- "<<it->second->size()<<" "<<n_nodes<<" "<<it->first<<std::endl;
				vtkFloatArray *data = vtkFloatArray::New();
				data->SetName(it->first.c_str());
				std::vector<Number> values;
				it->second->localize(values);
				data->SetNumberOfValues(n_nodes);


	//         MeshBase::const_node_iterator it = mesh.active_nodes_begin();
	//         const MeshBase::const_node_iterator n_end = mesh.nodes_end();
	//         for(unsigned int count=0;it!=n_end;++it,++count){
				for(unsigned int j=0;j<n_nodes;++j){
					libMesh::out<<"j "<<j<<" "<<(*it->second).size()<<std::endl;
//               const unsigned int dof_nr = mesh.node(j).dof_number(i,0,0);
					data->SetValue(j,values[j]);
	//            it++;
				}
				grid->GetPointData()->AddArray(data);
			}
		}
	}*/
}

/*
// write out mesh data to the VTK file, this might come in handy to display
// boundary conditions and material data
inline void meshdata_to_vtk(const MeshData& meshdata,
										vtkUnstructuredGrid* grid){
	vtkPointData* pointdata = vtkPointData::New();

	const unsigned int n_vn = meshdata.n_val_per_node();
	const unsigned int n_dat = meshdata.n_node_data();

	pointdata->SetNumberOfTuples(n_dat);
}*/

#endif


// ------------------------------------------------------------
// vtkIO class members
//
void VTKIO::read (const std::string& name)
{
  // This is a serial-only process for now;
  // the Mesh should be read on processor 0 and
  // broadcast later
  libmesh_assert_equal_to (libMesh::processor_id(), 0);

  // Keep track of what kinds of elements this file contains
  elems_of_dimension.clear();
  elems_of_dimension.resize(4, false);

#ifndef LIBMESH_HAVE_VTK
  libMesh::err << "Cannot read VTK file: " << name
	        << "\nYou must have VTK installed and correctly configured to read VTK meshes."
	        << std::endl;
  libmesh_error();

#else
//  libMesh::out<<"read "<<name <<std::endl;
  vtkXMLUnstructuredGridReader *reader = vtkXMLUnstructuredGridReader::New();
  reader->SetFileName( name.c_str() );
  //libMesh::out<<"force read"<<std::endl;
  // Force reading
  reader->Update();

  // read in the grid
//  vtkUnstructuredGrid *grid = reader->GetOutput();
  _vtk_grid = reader->GetOutput();
  _vtk_grid->Update();
  reader->Delete();

  // Get a reference to the mesh
  MeshBase& mesh = MeshInput<MeshBase>::mesh();

  // Clear out any pre-existing data from the Mesh
  mesh.clear();

  // always numbered nicely??, so we can loop like this
  // I'm pretty sure it is numbered nicely
  for (unsigned int i=0; i < static_cast<unsigned int>(_vtk_grid->GetNumberOfPoints()); ++i)
    {
      // add to the id map
      // and add the actual point
      double * pnt = _vtk_grid->GetPoint(static_cast<vtkIdType>(i));
      Point xyz(pnt[0],pnt[1],pnt[2]);
      Node* newnode = mesh.add_point(xyz,i);

      // Add node to the nodes vector &
      // tell the MeshData object the foreign node id.
      if (this->_mesh_data != NULL)
	this->_mesh_data->add_foreign_node_id (newnode, i);
    }

  for (unsigned int i=0; i < static_cast<unsigned int>(_vtk_grid->GetNumberOfCells()); ++i)
    {
      vtkCell* cell = _vtk_grid->GetCell(i);
      Elem* elem = NULL;  // Initialize to avoid compiler warning
      switch(cell->GetCellType())
	{
        // FIXME - we're not supporting 2D VTK input yet!? [RHS]
	case VTK_TETRA:
	  elem = new Tet4();
	  break;
	case VTK_WEDGE:
	  elem = new Prism6();
	  break;
	case VTK_HEXAHEDRON:
	  elem = new Hex8();
	  break;
	case VTK_PYRAMID:
	  elem = new Pyramid5();
	  break;
	case VTK_QUADRATIC_HEXAHEDRON:
  	  elem = new Hex20();
	  break;
	case VTK_QUADRATIC_TETRA:
	  elem = new Tet10();
	  break;
	default:
	  libMesh::err << "element type not implemented in vtkinterface " << cell->GetCellType() << std::endl;
	  libmesh_error();
	}

  // get the straightforward numbering from the VTK cells
  for(unsigned int j=0;j<elem->n_nodes();++j){
	  elem->set_node(j) = mesh.node_ptr(cell->GetPointId(j));
  }
  // then get the connectivity
  std::vector<unsigned int> conn;
  elem->connectivity(0,VTK,conn);
  // then reshuffle the nodes according to the connectivity, this
  // two-time-assign would evade the definition of the vtk_mapping
  for(unsigned int j=0;j<conn.size();++j){
	  elem->set_node(j) = mesh.node_ptr(conn[j]);
  }
  elem->set_id(i);

  elems_of_dimension[elem->dim()] = true;

  mesh.add_elem(elem);
  } // end loop over VTK cells
  _vtk_grid->Delete();

  // Set the mesh dimension to the largest encountered for an element
  for (unsigned int i=0; i!=4; ++i)
    if (elems_of_dimension[i])
      mesh.set_mesh_dimension(i);

#if LIBMESH_DIM < 3
  if (mesh.mesh_dimension() > LIBMESH_DIM)
    {
      libMesh::err << "Cannot open dimension " <<
		      mesh.mesh_dimension() <<
		      " mesh file when configured without " <<
                      mesh.mesh_dimension() << "D support." <<
                      std::endl;
      libmesh_error();
    }
#endif

#endif // LIBMESH_HAVE_VTK
}

/*
 * FIXME this operates on the mesh it "gets" from the ES only, this would
 * prevent passing in a mesh that doesn't belong to the ES
 */
/**
 * This method writes out the equationsystems to a .pvtu file (VTK parallel
 * unstructured grid).
 */
void VTKIO::write_equation_systems(const std::string& fname,
                                   const EquationSystems& es,
                                   const std::set<std::string>*)
{
#ifndef LIBMESH_HAVE_VTK

  // Do something with the es to avoid a compiler warning.
  es.n_systems();

  libMesh::err << "Cannot write VTK file: " << fname
	        << "\nYou must have VTK installed and correctly configured to read VTK meshes."
	        << std::endl;
  libmesh_error();

#else

  // We may need to gather a ParallelMesh to output it, making that
  // const qualifier in our constructor a dirty lie
  MeshSerializer serialize(const_cast<MeshBase&>(MeshOutput<MeshBase>::mesh()),
                           !MeshOutput<MeshBase>::_is_parallel_format);

  vtkXMLPUnstructuredGridWriter* writer = 0;
  vtkPoints* pnts = 0;
  vtkCellArray* cells = 0;
  std::vector<int> types;

  const MeshBase& mesh = es.get_mesh();

  unsigned int n_active_elem = mesh.n_active_elem();

  if (libMesh::processor_id() == 0){

	  // check if the filename extension is pvtu
	  libmesh_assert_equal_to (fname.substr(fname.rfind("."),fname.size()), ".pvtu");

	  /*
		* we only use Unstructured grids
		*/
	  _vtk_grid = vtkUnstructuredGrid::New();
	  writer = vtkXMLPUnstructuredGridWriter::New();
//	  libMesh::out<<"get points "<<std::endl;
	  pnts = nodes_to_vtk(mesh);
	  _vtk_grid->SetPoints(pnts);

	  types.resize(n_active_elem);
//	  libMesh::out<<"get cells"<<std::endl;
	  cells = cells_to_vtk(mesh, types);
//	  libMesh::out<<"set cells"<<std::endl;
	  _vtk_grid->SetCells(&types[0],cells);
  }

	  // I'd like to write out meshdata, but this requires some coding, in
	  // particular, non_const meshdata iterators
	  //   const MeshData& md = es.get_mesh_data();
	  //   if(es.has_mesh_data())
	  //      meshdata_to_vtk(md,_vtk_grid);
	  //   libmesh_assert_equal_to (soln.size(), mesh.n_nodes()*names.size());
//	  libMesh::out<<"write solution"<<std::endl;

          // call solution_to_vtk on all processors to allow localization of the solution in it
	  solution_to_vtk(es,_vtk_grid);

//#ifdef DEBUG
//     if(true) // add some condition here, although maybe it is more sensible to give each vector a flag on whether it is to be written out or not
//       system_vectors_to_vtk(es,_vtk_grid);
//#endif

  if (libMesh::processor_id() == 0){
	  writer->SetInput(_vtk_grid);
	  writer->SetFileName(fname.c_str());
	  writer->SetDataModeToAscii();
	  writer->Write();

	  pnts->Delete();
	  cells->Delete();
	  _vtk_grid->Delete();
	  writer->Delete();
  }
#endif
}

/**
 * This method implements writing to a .vtu (VTK Unstructured Grid) file.
 * This is one of the new style XML dataformats.
 */
void VTKIO::write (const std::string& name)
{
#ifndef LIBMESH_HAVE_VTK
  libMesh::err << "Cannot write VTK file: " << name
	        << "\nYou must have VTK installed and correctly configured to write VTK meshes."
	        << std::endl;
  libmesh_error();

#else
  const MeshBase& mesh = MeshOutput<MeshBase>::mesh();
  unsigned int n_active_elem = mesh.n_active_elem();

  // We may need to gather a ParallelMesh to output it, making that
  // const qualifier in our constructor a dirty lie
  MeshSerializer serialize(const_cast<MeshBase&>(MeshOutput<MeshBase>::mesh()),
                           !MeshOutput<MeshBase>::_is_parallel_format);

  if (libMesh::processor_id() == 0)
  {

	  _vtk_grid = vtkUnstructuredGrid::New();
	  vtkXMLUnstructuredGridWriter* writer = vtkXMLUnstructuredGridWriter::New();
//	  libMesh::out<<"write nodes "<<std::endl;
	  vtkPoints* pnts = nodes_to_vtk(mesh);
	  _vtk_grid->SetPoints(pnts);

//	  libMesh::out<<"write elements "<<std::endl;
	  std::vector<int> types(n_active_elem);
	  vtkCellArray* cells = cells_to_vtk(mesh,types);
	  _vtk_grid->SetCells(&types[0],cells);
	  //  , _vtk_grid);
	  writer->SetInput(_vtk_grid);
	  writer->SetDataModeToAscii();
	  writer->SetFileName(name.c_str());
	  writer->Write();
	  writer->Delete();
	  _vtk_grid->Delete();
  }
#endif // LIBMESH_HAVE_VTK
}

} // namespace libMesh

//  vim: sw=3 ts=3

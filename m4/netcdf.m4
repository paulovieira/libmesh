dnl -------------------------------------------------------------
dnl netCDF
dnl -------------------------------------------------------------
AC_DEFUN([CONFIGURE_NETCDF], 
[
  AC_ARG_ENABLE(netcdf,
                AC_HELP_STRING([--enable-netcdf],
                               [build with netCDF binary I/O]),
		[case "${enableval}" in
		  yes)  enablenetcdf=yes ;;
		   no)  enablenetcdf=no ;;
 		    *)  AC_MSG_ERROR(bad value ${enableval} for --enable-netcdf) ;;
		 esac],
		 [enablenetcdf=$enableoptional])


				
  dnl The NETCDF API is distributed with libmesh, so we don't have to guess
  dnl where it might be installed...
  if (test $enablenetcdf = yes); then
     NETCDF_INCLUDE="-I\$(top_srcdir)/contrib/netcdf/Lib"
     NETCDF_LIBRARY="\$(EXTERNAL_LIBDIR)/libnetcdf\$(libext)"
     AC_DEFINE(HAVE_NETCDF, 1, [Flag indicating whether the library will be compiled with Netcdf support])
     AC_MSG_RESULT(<<< Configuring library with Netcdf support >>>)
     have_netcdf=yes
  else
     NETCDF_INCLUDE=""
     NETCDF_LIBRARY=""
     enablenetcdf=no
     have_netcdf=no
  fi

  AC_SUBST(NETCDF_INCLUDE)
  AC_SUBST(NETCDF_LIBRARY)	
])

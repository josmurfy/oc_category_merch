<?php
// Heading
$_['heading_title']    = 'Gestor Merch Categorías';

// Text
$_['text_extension'] = 'Extensiones';
$_['text_success'] = '¡Éxito: has modificado los ajustes de Gestor Merch Categorías!';
$_['text_edit'] = 'Editar Gestor Merch Categorías';
$_['text_enabled'] = 'Habilitado';
$_['text_disabled'] = 'Deshabilitado';
$_['text_auto'] = 'Auto';
$_['text_force_show'] = 'Forzar mostrar';
$_['text_force_hide'] = 'Forzar ocultar';
$_['text_dashboard'] = 'Panel de categorías';
$_['text_category_tree'] = 'Árbol de categorías — puntuaciones en profundidad';
$_['text_category_tree_hint'] = 'Haz clic en una categoría para expandirla y ver la puntuación de sus hijas, a cualquier profundidad (sub, sub-sub, etc.).';
$_['text_leaf_categories'] = 'Tus categorías con más stock';
$_['text_leaf_categories_hint'] = 'Las categorías más específicas que realmente tienen stock — ahí es donde llegan tus compradores, no en las categorías genéricas.';
$_['text_general_view'] = 'Vista general (categorías padre)';
$_['text_empty_cleanup_title'] = 'Categorías vacías que ensucian tu catálogo';
$_['text_empty_cleanup_none'] = 'No se encontraron categorías vacías — tu catálogo está limpio.';
$_['button_hide_all_empty'] = 'Ocultar todas';
$_['text_confirm_hide_all_empty'] = 'Esto ocultará forzosamente todas las categorías actualmente con 0 productos activos de la tienda. Puedes deshacer cada una individualmente en la pestaña Overrides. ¿Continuar?';
$_['text_hide_all_empty_done'] = 'Listo — %d categorías vacías ocultadas.';
$_['text_no_results'] = 'No se encontraron categorías.';

// Pestañas
$_['tab_settings'] = 'Ajustes';
$_['tab_dashboard'] = 'Panel';
$_['tab_overrides'] = 'Overrides';
$_['tab_updates'] = 'Actualizaciones';

// Entradas
$_['entry_status'] = 'Estado del módulo';
$_['entry_hide_empty'] = 'Ocultar categorías vacías';
$_['entry_hide_empty_subs'] = 'Ocultar subcategorías vacías';
$_['entry_sort_by_score'] = 'Ordenar categorías por puntuación';
$_['entry_weight_volume'] = 'Peso volumen (%)';
$_['entry_cache_ttl'] = 'TTL caché menú (segundos)';
$_['entry_override'] = 'Override';

// Columnas
$_['column_name'] = 'Categoría';
$_['column_total'] = 'Productos activos (subárbol)';
$_['column_score'] = 'Puntuación (%)';
$_['column_override'] = 'Override';
$_['column_status'] = 'Estado';

// Ayuda
$_['help_hide_empty'] = 'Cuando está habilitado, las categorías y subcategorías con 0 productos activos se ocultan en el menú frontal.';
$_['help_hide_empty_subs'] = 'Cuando está habilitado, las subcategorías con 0 productos activos se ocultan dentro de su categoría padre. Independiente del toggle de categorías top-level.';
$_['help_sort_by_score'] = 'Cuando está habilitado, las categorías se ordenan por puntuación de merchandising (número de productos).';
$_['help_cache_ttl'] = 'Vida útil del caché de reordenar/ocultar menú. Valores más bajos refrescan más rápido; más altos reducen carga en la BD.';

// Botones
$_['button_save'] = 'Guardar';
$_['button_cancel'] = 'Cancelar';
$_['button_recalculate'] = 'Recalcular / Refrescar caché';
$_['button_check_updates'] = 'Buscar actualizaciones';
$_['button_install_update'] = 'Instalar actualización';
$_['button_download'] = 'Descargar release';
$_['button_view_release'] = 'Ver release';
$_['button_refresh'] = 'Refrescar';

// Actualizaciones
$_['text_updates_intro'] = 'Este módulo puede actualizarse desde GitHub. Haga clic abajo para buscar una nueva versión.';
$_['text_current_version'] = 'Versión instalada';
$_['text_latest_version'] = 'Última versión';
$_['text_up_to_date'] = 'Está usando la última versión.';
$_['text_update_available'] = '¡Hay una actualización disponible!';
$_['text_repository'] = 'Repositorio';
$_['text_no_repository'] = 'No hay repositorio GitHub configurado para este módulo.';
$_['text_checking'] = 'Verificando...';
$_['text_session_expired'] = 'Tu sesión de administrador ha expirado. Recarga esta página y vuelve a iniciar sesión antes de buscar actualizaciones.';
$_['text_changelog'] = 'Registro de cambios';
$_['text_update_source'] = 'Las actualizaciones se verifican desde GitHub:';
$_['text_installing'] = 'Descargando e instalando la actualización...';
$_['text_version_history'] = 'Historial de versiones';
$_['text_version_history_hint'] = 'Haz clic en la pestaña Actualizaciones para cargar el historial de versiones.';
$_['text_version_installed'] = 'INSTALADA';
$_['text_version_newer'] = 'NUEVA';
$_['text_version_downgrade'] = 'Instalar esta versión';
$_['text_confirm_downgrade'] = '¿Seguro que quieres instalar una versión anterior? Esto sobrescribirá la versión actual.';

// Errores
$_['error_permission'] = '¡Advertencia: no tiene permiso para modificar Gestor Merch Categorías!';
$_['error_update_check'] = 'No se pudo contactar con la API de GitHub. Inténtelo más tarde.';
$_['error_untrusted_url'] = 'Descarga rechazada: URL no confiable.';
$_['error_update_download'] = 'No se pudo descargar el archivo de actualización. Inténtalo más tarde.';
$_['error_update_extract'] = 'No se pudo extraer el archivo de actualización.';
$_['error_ext_dir_missing'] = 'Falta la carpeta de la extensión en el servidor.';
$_['error_update_write'] = 'Algunos archivos no se pudieron escribir durante la actualización.';

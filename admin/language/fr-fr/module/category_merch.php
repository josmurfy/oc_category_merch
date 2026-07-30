<?php
// Heading
$_['heading_title']    = 'Gestionnaire Merch Catégories';

// Text
$_['text_extension'] = 'Extensions';
$_['text_success'] = 'Succès : vous avez modifié les réglages de Gestionnaire Merch Catégories !';
$_['text_edit'] = 'Modifier Gestionnaire Merch Catégories';
$_['text_enabled'] = 'Activé';
$_['text_disabled'] = 'Désactivé';
$_['text_auto'] = 'Auto';
$_['text_force_show'] = 'Forcer affichage';
$_['text_force_hide'] = 'Forcer masquage';
$_['text_dashboard'] = 'Tableau de bord catégories';
$_['text_category_tree'] = 'Arbre des catégories — scores en profondeur';
$_['text_category_tree_hint'] = 'Clique sur une catégorie pour la déplier et voir le score de ses enfants, peu importe la profondeur (sous, sous-sous, etc.).';
$_['text_leaf_categories'] = 'Tes meilleures catégories (celles qui ont du stock)';
$_['text_leaf_categories_hint'] = 'Les catégories les plus précises qui ont vraiment du stock — c\'est là que tes acheteurs atterrissent, pas dans les gros paniers génériques.';
$_['text_general_view'] = 'Vue générale (catégories parentes)';
$_['text_empty_cleanup_title'] = 'Catégories vides qui encombrent ton catalogue';
$_['text_empty_cleanup_none'] = 'Aucune catégorie vide trouvée — ton catalogue est propre.';
$_['button_hide_all_empty'] = 'Masquer tout';
$_['text_confirm_hide_all_empty'] = 'Ça va forcer le masquage de toutes les catégories actuellement à 0 produit actif sur le site. Tu peux annuler chacune individuellement dans l\'onglet Overrides. Continuer ?';
$_['text_hide_all_empty_done'] = 'Fait — %d catégories vides masquées.';
$_['text_no_results'] = 'Aucune catégorie trouvée.';

// Onglets
$_['tab_settings'] = 'Réglages';
$_['tab_dashboard'] = 'Tableau de bord';
$_['tab_overrides'] = 'Overrides';
$_['tab_updates'] = 'Mises à jour';

// Entrées
$_['entry_status'] = 'Statut du module';
$_['entry_hide_empty'] = 'Masquer les catégories vides';
$_['entry_hide_empty_subs'] = 'Masquer les sous-catégories vides';
$_['entry_sort_by_score'] = 'Trier les catégories par score';
$_['entry_weight_volume'] = 'Poids volume (%)';
$_['entry_cache_ttl'] = 'TTL cache menu (secondes)';
$_['entry_override'] = 'Override';

// Colonnes
$_['column_name'] = 'Catégorie';
$_['column_total'] = 'Produits actifs (sous-arbre)';
$_['column_score'] = 'Score (%)';
$_['column_override'] = 'Override';
$_['column_status'] = 'Statut';

// Aide
$_['help_hide_empty'] = 'Quand activé, les catégories et sous-catégories avec 0 produit actif sont masquées dans le menu front.';
$_['help_hide_empty_subs'] = 'Quand activé, les sous-catégories avec 0 produit actif sont masquées à l\'intérieur de leur parent. Indépendant du toggle des catégories top-level.';
$_['help_sort_by_score'] = 'Quand activé, les catégories sont ordonnées par score merchandising (nombre de produits).';
$_['help_cache_ttl'] = 'Durée de vie du cache tri/filtrage menu. Plus bas = plus frais ; plus haut = moins de charge DB.';

// Boutons
$_['button_save'] = 'Enregistrer';
$_['button_cancel'] = 'Annuler';
$_['button_recalculate'] = 'Recalculer / Rafraîchir le cache';
$_['button_check_updates'] = 'Vérifier les mises à jour';
$_['button_install_update'] = 'Installer la mise à jour';
$_['button_download'] = 'Télécharger la release';
$_['button_view_release'] = 'Voir la release';
$_['button_refresh'] = 'Rafraîchir';

// Mises à jour
$_['text_updates_intro'] = 'Ce module peut être mis à jour depuis GitHub. Cliquez ci-dessous pour vérifier une nouvelle version.';
$_['text_current_version'] = 'Version installée';
$_['text_latest_version'] = 'Dernière version';
$_['text_up_to_date'] = 'Vous utilisez déjà la dernière version.';
$_['text_update_available'] = 'Une mise à jour est disponible !';
$_['text_repository'] = 'Dépôt';
$_['text_no_repository'] = 'Aucun dépôt GitHub configuré pour ce module.';
$_['text_checking'] = 'Vérification...';
$_['text_session_expired'] = 'Ta session admin a expiré. Recharge cette page et reconnecte-toi avant de vérifier les mises à jour.';
$_['text_changelog'] = 'Changelog';
$_['text_update_source'] = 'Les mises à jour sont vérifiées depuis GitHub :';
$_['text_installing'] = 'Téléchargement et installation de la mise à jour...';
$_['text_version_history'] = 'Historique des versions';
$_['text_version_history_hint'] = 'Clique sur l\'onglet Mises à jour pour charger l\'historique des versions.';
$_['text_version_installed'] = 'INSTALLÉE';
$_['text_version_newer'] = 'NOUVELLE';
$_['text_version_downgrade'] = 'Installer cette version';
$_['text_confirm_downgrade'] = 'Es-tu sûr de vouloir installer une version plus ancienne ? Ça va écraser la version actuelle.';

// Erreurs
$_['error_permission'] = 'Attention : vous n\'avez pas la permission de modifier Gestionnaire Merch Catégories !';
$_['error_update_check'] = 'Impossible de joindre l\'API GitHub. Réessayez plus tard.';
$_['error_untrusted_url'] = 'Téléchargement refusé : URL non fiable.';
$_['error_update_download'] = 'Impossible de télécharger l\'archive de mise à jour. Réessaie plus tard.';
$_['error_update_extract'] = 'Impossible d\'extraire l\'archive de mise à jour.';
$_['error_ext_dir_missing'] = 'Le dossier de l\'extension est manquant sur le serveur.';
$_['error_update_write'] = 'Certains fichiers n\'ont pas pu être écrits pendant la mise à jour.';

/*
 This file is intended for customising the way the attachment UI operates/defaults.

 The following variables are defined:
 is_image (boolean)
 is_video (boolean)
 is_audio (boolean)
 is_archive (boolean)
 ext (the file extension, with no dot)
 */

// Add any defaults into URL
defaults.thumb = ($cms.$CONFIG_OPTION('simplified_attachments_ui') && isImage && !multi) ? '0' : '1';
defaults.type = ''; // =autodetect rendering type

// Shall we show the options overlay?
showOverlay = !(multi || (isImage && $cms.$CONFIG_OPTION('simplified_attachments_ui')) || isArchive);

if (isImage) {
    tag = 'attachment_safe';
}

if (multi || isImage) {
    defaults.framed = '0';
}
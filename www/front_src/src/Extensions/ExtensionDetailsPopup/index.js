/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/prefer-stateless-function */

import React from 'react';

import Carousel from 'react-material-ui-carousel';
import { Responsive } from '@visx/visx';

import ChevronLeftIcon from '@material-ui/icons/ChevronLeft';
import ChevronRightIcon from '@material-ui/icons/ChevronRight';
import {
  Chip,
  Typography,
  Divider,
  Grid,
  Button,
  Link,
} from '@material-ui/core';
import UpdateIcon from '@material-ui/icons/SystemUpdateAlt';
import DeleteIcon from '@material-ui/icons/Delete';
import InstallIcon from '@material-ui/icons/Add';

import { Dialog, IconButton } from '@centreon/ui';

import {
  SliderSkeleton,
  HeaderSkeleton,
  ContentSkeleton,
  ReleaseNoteSkeleton,
} from './LoadingSkeleton';

class ExtensionDetailPopup extends React.Component {
  render() {
    const {
      modalDetails,
      onCloseClicked,
      onDeleteClicked,
      onUpdateClicked,
      onInstallClicked,
      loading,
      animate,
    } = this.props;

    if (modalDetails === null) {
      return null;
    }

    return (
      <Dialog
        open
        labelConfirm="Close"
        onClose={onCloseClicked}
        onConfirm={onCloseClicked}
      >
        <Grid container direction="column" spacing={2} style={{ width: 520 }}>
          <Grid item style={{ height: 300 }}>
            <Responsive.ParentSize>
              {({ width }) =>
                loading ? (
                  <SliderSkeleton animate={animate} width={width} />
                ) : (
                  <Carousel
                    NextIcon={<ChevronRightIcon />}
                    PrevIcon={<ChevronLeftIcon />}
                    animation="slide"
                    autoPlay={false}
                  >
                    {modalDetails.images.map((image) => (
                      <img alt={image} key={image} src={image} width={width} />
                    ))}
                  </Carousel>
                )
              }
            </Responsive.ParentSize>
          </Grid>
          <Grid item>
            {modalDetails.version.installed && modalDetails.version.outdated ? (
              <IconButton
                onClick={() => {
                  onUpdateClicked(modalDetails.id, modalDetails.type);
                }}
              >
                <UpdateIcon />
              </IconButton>
            ) : null}
            {modalDetails.version.installed ? (
              <Button
                color="primary"
                disabled={loading}
                size="small"
                startIcon={<DeleteIcon />}
                variant="contained"
                onClick={() => {
                  onDeleteClicked(modalDetails.id, modalDetails.type);
                }}
              >
                Delete
              </Button>
            ) : (
              <Button
                color="primary"
                disabled={!loading}
                size="small"
                startIcon={<InstallIcon />}
                variant="contained"
                onClick={() => {
                  onInstallClicked(modalDetails.id, modalDetails.type);
                }}
              >
                Install
              </Button>
            )}
          </Grid>
          <Grid item>
            {loading ? (
              <HeaderSkeleton animate={animate} />
            ) : (
              <>
                <Typography variant="h5">{modalDetails.title}</Typography>
                <Grid container spacing={1}>
                  <Grid item>
                    <Chip
                      label={
                        (!modalDetails.version.installed ? 'Available ' : '') +
                        modalDetails.version.available
                      }
                    />
                  </Grid>
                  <Grid item>
                    <Chip label={modalDetails.stability} />
                  </Grid>
                </Grid>
              </>
            )}
          </Grid>
          <Grid item>
            {loading ? (
              <ContentSkeleton animate={animate} />
            ) : (
              <>
                {modalDetails.last_update ? (
                  <Typography variant="body1">{`Last update ${modalDetails.last_update}`}</Typography>
                ) : null}
                <Typography variant="h6">Description</Typography>
                <Typography variant="body2">
                  {modalDetails.description}
                </Typography>
              </>
            )}
          </Grid>
          <Grid item>
            <Divider />
          </Grid>
          <Grid item>
            {loading ? (
              <ReleaseNoteSkeleton animate={animate} />
            ) : (
              <Link href={modalDetails.release_note}>
                <Typography>{modalDetails.release_note}</Typography>
              </Link>
            )}
          </Grid>
        </Grid>
      </Dialog>
    );
  }
}

export default ExtensionDetailPopup;

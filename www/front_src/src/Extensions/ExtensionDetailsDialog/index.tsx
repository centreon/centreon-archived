/* eslint-disable react/jsx-filename-extension */
/* eslint-disable react/prop-types */
/* eslint-disable react/prefer-stateless-function */

import React, { useState, useEffect } from 'react';

import Carousel from 'react-material-ui-carousel';
import { Responsive } from '@visx/visx';

import ChevronLeftIcon from '@mui/icons-material/ChevronLeft';
import ChevronRightIcon from '@mui/icons-material/ChevronRight';
import { Chip, Typography, Divider, Grid, Button, Link } from '@mui/material';
import UpdateIcon from '@mui/icons-material/SystemUpdateAlt';
import DeleteIcon from '@mui/icons-material/Delete';
import InstallIcon from '@mui/icons-material/Add';

import { Dialog, IconButton, useRequest, getData } from '@centreon/ui';

import { Entity } from '../models';
import buildEndPoint from '../api/endpoint';

import {
  SliderSkeleton,
  HeaderSkeleton,
  ContentSkeleton,
  ReleaseNoteSkeleton,
} from './LoadingSkeleton';

interface Props {
  id: string;
  onCloseClicked: () => void;
  onDeleteClicked: (id: string, type: string, description: string) => void;
  onInstallClicked: (id: string, type: string) => void;
  onUpdateClicked: (id: string, type: string) => void;
  type: string;
}

interface ExtensionDetails {
  result: Entity;
  status: boolean;
}

const ExtensionDetailPopup = ({
  id,
  type,
  onCloseClicked,
  onDeleteClicked,
  onUpdateClicked,
  onInstallClicked,
}: Props): JSX.Element | null => {
  const [extensionDetails, setExtensionDetails] = useState<Entity | null>(null);
  const [loading, setLoading] = useState<boolean>(true);

  const { sendRequest: sendExtensionDetailsValueRequests } =
    useRequest<ExtensionDetails>({
      request: getData,
    });

  useEffect(() => {
    sendExtensionDetailsValueRequests({
      endpoint: buildEndPoint({
        action: 'details',
        id,
        type,
      }),
    }).then((data) => {
      const { result } = data;
      if (result.images) {
        result.images = result.images.map((image) => {
          return `./${image}`;
        });
      }
      setExtensionDetails(result);
      setLoading(false);
    });
  }, []);

  if (extensionDetails === null) {
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
            {({ width }): JSX.Element =>
              loading ? (
                <SliderSkeleton width={width} />
              ) : (
                <Carousel
                  fullHeightHover
                  NextIcon={<ChevronRightIcon />}
                  PrevIcon={<ChevronLeftIcon />}
                  animation="slide"
                  autoPlay={false}
                >
                  {extensionDetails.images
                    ? extensionDetails.images.map((image) => (
                        <img
                          alt={image}
                          key={image}
                          src={image}
                          width={width}
                        />
                      ))
                    : null}
                </Carousel>
              )
            }
          </Responsive.ParentSize>
        </Grid>
        <Grid item>
          {extensionDetails.version.installed &&
          extensionDetails.version.outdated ? (
            <IconButton
              size="large"
              onClick={(): void => {
                onUpdateClicked(extensionDetails.id, extensionDetails.type);
              }}
            >
              <UpdateIcon />
            </IconButton>
          ) : null}
          {extensionDetails.version.installed ? (
            <Button
              color="primary"
              disabled={loading}
              size="small"
              startIcon={<DeleteIcon />}
              variant="contained"
              onClick={(): void => {
                onDeleteClicked(
                  extensionDetails.id,
                  extensionDetails.type,
                  extensionDetails.title,
                );
              }}
            >
              Delete
            </Button>
          ) : (
            <Button
              color="primary"
              disabled={loading}
              size="small"
              startIcon={<InstallIcon />}
              variant="contained"
              onClick={(): void => {
                onInstallClicked(extensionDetails.id, extensionDetails.type);
              }}
            >
              Install
            </Button>
          )}
        </Grid>
        <Grid item>
          {loading ? (
            <HeaderSkeleton />
          ) : (
            <>
              <Typography variant="h5">{extensionDetails.title}</Typography>
              <Grid container spacing={1}>
                <Grid item>
                  <Chip
                    label={
                      (!extensionDetails.version.installed
                        ? 'Available '
                        : '') + extensionDetails.version.available
                    }
                  />
                </Grid>
                <Grid item>
                  <Chip label={extensionDetails.stability} />
                </Grid>
              </Grid>
            </>
          )}
        </Grid>
        <Grid item>
          {loading ? (
            <ContentSkeleton />
          ) : (
            <>
              {extensionDetails.last_update ? (
                <Typography variant="body1">{`Last update ${extensionDetails.last_update}`}</Typography>
              ) : null}
              <Typography variant="h6">Description</Typography>
              <Typography variant="body2">
                {extensionDetails.description}
              </Typography>
            </>
          )}
        </Grid>
        <Grid item>
          <Divider />
        </Grid>
        <Grid item>
          {loading ? (
            <ReleaseNoteSkeleton />
          ) : (
            <Link href={extensionDetails.release_note}>
              <Typography>{extensionDetails.release_note}</Typography>
            </Link>
          )}
        </Grid>
      </Grid>
    </Dialog>
  );
};

export default ExtensionDetailPopup;
